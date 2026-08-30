<?php

namespace App\Services\SiteAi\Proposals;

use App\Models\Ai\AiProposal;
use App\Models\Website;
use App\Services\SiteAi\Proposals\Contracts\AiProposalDestination;
use RuntimeException;

class AiProposalDestinationRegistry
{
    /**
     * @var array<int,AiProposalDestination>
     */
    protected array $destinations = [];


    public function register(
        AiProposalDestination $destination
    ): void {
        $this->destinations[] =
            $destination;
    }


    public function resolve(
        ?Website $website,
        AiProposal $proposal
    ): AiProposalDestination {
        foreach (
            $this->destinations
            as $destination
        ) {
            if (
                $destination->supports(
                    $website,
                    $proposal
                )
            ) {
                return $destination;
            }
        }

        throw new RuntimeException(
            'No AI proposal destination transport is registered for this context.'
        );
    }


    /**
     * @return array<int,AiProposalDestination>
     */
    public function all(): array
    {
        return $this->destinations;
    }
}
