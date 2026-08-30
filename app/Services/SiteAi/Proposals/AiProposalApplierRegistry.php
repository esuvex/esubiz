<?php

namespace App\Services\SiteAi\Proposals;

use App\Services\SiteAi\Proposals\Contracts\AiProposalApplier;
use RuntimeException;

class AiProposalApplierRegistry
{
    /**
     * @var array<string,AiProposalApplier>
     */
    protected array $appliers = [];


    public function register(
        AiProposalApplier $applier
    ): void {
        $capability =
            trim(
                $applier->capability()
            );

        if ($capability === '') {
            throw new RuntimeException(
                'AI proposal applier capability cannot be empty.'
            );
        }

        $this->appliers[
            $capability
        ] =
            $applier;
    }


    public function has(
        string $capability
    ): bool {
        return isset(
            $this->appliers[
                $capability
            ]
        );
    }


    public function get(
        string $capability
    ): AiProposalApplier {
        $capability =
            trim(
                $capability
            );

        if (!$this->has($capability)) {
            throw new RuntimeException(
                'No AI proposal applier is registered for capability: '
                . $capability
            );
        }

        return $this->appliers[
            $capability
        ];
    }


    /**
     * @return array<string,AiProposalApplier>
     */
    public function all(): array
    {
        return $this->appliers;
    }
}
