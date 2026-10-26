<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\DepositStatus;
use App\Models\Deposit;
use App\Models\WasteCategory;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'kategori' => ['nullable', 'integer', 'min:1'],
        ]);

        [$from, $to] = $this->resolveRange($data['dari'] ?? null, $data['sampai'] ?? null);
        $categoryId = isset($data['kategori']) ? (int) $data['kategori'] : null;

        $base = $this->baseQuery($from, $to, $categoryId);

        $summary = (clone $base)
            ->selectRaw('COUNT(*) as jumlah')
            ->selectRaw('COALESCE(SUM(deposits.total_points), 0) as poin')
            ->first();

        $totalKg = (clone $base)
            ->join('deposit_items', 'deposit_items.deposit_id', '=', 'deposits.id')
            ->selectRaw('COALESCE(SUM(deposit_items.weight_in_kg), 0) as kg')
            ->value('kg');

        $deposits = (clone $base)
            ->with(['user', 'items.wasteCategory'])
            ->orderByDesc('deposits.created_at')
            ->limit(300)
            ->get();

        $ledgerSummary = $this->ledgerSummary($from, $to);

        return view('reports.index', [
            'from' => $from,
            'to' => $to,
            'categoryId' => $categoryId,
            'categories' => WasteCategory::query()->orderBy('name')->get(['id', 'name']),
            'jumlahTransaksi' => (int) $summary->jumlah,
            'totalPoin' => (int) $summary->poin,
            'totalKg' => (float) $totalKg,
            'deposits' => $deposits,
            'ledgerKredit' => (int) $ledgerSummary->kredit,
            'ledgerDebit' => (int) $ledgerSummary->debit,
            'pointValue' => (int) config('kas-hijau.point_value', 0),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date'],
            'kategori' => ['nullable', 'integer', 'min:1'],
        ]);

        [$from, $to] = $this->resolveRange($data['dari'] ?? null, $data['sampai'] ?? null);
        $categoryId = isset($data['kategori']) ? (int) $data['kategori'] : null;

        $rows = $this->baseQuery($from, $to, $categoryId)
            ->with(['user', 'items.wasteCategory'])
            ->orderByDesc('deposits.created_at')
            ->get();

        $filename = sprintf(
            'laporan-setoran-%s_sd_%s.csv',
            $from->format('Ymd'),
            $to->format('Ymd'),
        );

        $pointValue = (int) config('kas-hijau.point_value', 0);

        return response()->streamDownload(function () use ($rows, $from, $to, $pointValue): void {
            $out = fopen('php://output', 'wb');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Laporan Setoran - Kas Hijau'], ';');
            fputcsv($out, ['Periode', $from->format('d/m/Y').' s.d. '.$to->format('d/m/Y')], ';');
            fputcsv($out, ['Diunduh', now()->format('d/m/Y H:i')], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['No', 'Kode Setoran', 'Tanggal', 'Warga', 'Rincian', 'Berat (kg)', 'Poin', 'Nilai (Rp)'], ';');

            $no = 1;
            foreach ($rows as $deposit) {
                $rincian = $deposit->items
                    ->map(fn ($item) => sprintf(
                        '%s (%s kg)',
                        $item->wasteCategory->name,
                        number_format((float) $item->weight_in_kg, 2, ',', '.'),
                    ))
                    ->implode(', ');

                fputcsv($out, [
                    $no++,
                    $deposit->deposit_code,
                    $deposit->created_at?->format('d/m/Y H:i'),
                    $deposit->user?->name,
                    $rincian,
                    number_format((float) $deposit->items->sum('weight_in_kg'), 2, ',', '.'),
                    number_format((int) $deposit->total_points, 0, ',', '.'),
                    number_format((int) $deposit->total_points * $pointValue, 0, ',', '.'),
                ], ';');
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveRange(?string $dari, ?string $sampai): array
    {
        $from = $dari !== null ? Carbon::parse($dari) : now()->subDays(29);
        $to = $sampai !== null ? Carbon::parse($sampai) : now();

        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    private function baseQuery($from, $to, ?int $categoryId)
    {
        $query = Deposit::query()
            ->where('status', DepositStatus::Completed->value)
            ->whereBetween('deposits.created_at', [
                $from->copy()->startOfDay(),
                $to->copy()->endOfDay(),
            ]);

        if ($categoryId !== null) {
            $query->whereHas('items', fn ($q) => $q->where('waste_category_id', $categoryId));
        }

        return $query;
    }

    private function ledgerSummary($from, $to): object
    {
        return DB::table('point_ledgers')
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as kredit")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as debit")
            ->first();
    }
}
