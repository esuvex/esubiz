<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreditTransactionHistoryService
{
    public function page(int $websiteId, string $creditType, int $page = 1): array
    {
        $service = [
            'ai_credits' => 'ai',
            'sms_credits' => 'sms',
            'email_credits' => 'email',
            'whatsapp_credits' => 'whatsapp',
            'kyc_credits' => 'kyc',
        ][$creditType] ?? null;

        if ($websiteId < 1 || $service === null) {
            throw new InvalidArgumentException('Invalid credit history request.');
        }

        $rows = DB::table('central_website_service_credit_transactions')
            ->where('website_id', $websiteId)
            ->where('service', $service)
            ->orderByDesc('id')
            ->paginate(10, [
                'id', 'direction', 'amount', 'balance_after',
                'source_type', 'created_at',
            ], 'page', max(1, $page));

        return [
            'items' => $rows->getCollection()->map(fn ($row) => [
                'id' => (int) $row->id,
                'type' => $row->direction === 'credit' ? 'Purchased' : 'Used',
                'amount' => (float) $row->amount,
                'balance_after' => (float) $row->balance_after,
                'source' => (string) ($row->source_type ?? ''),
                'date' => (string) $row->created_at,
            ])->all(),
            'page' => $rows->currentPage(),
            'has_previous' => $rows->currentPage() > 1,
            'has_next' => $rows->hasMorePages(),
        ];
    }
}
