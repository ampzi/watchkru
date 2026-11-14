<?php

namespace App\Filament\Resources\CommunityReviews\Pages;

use App\Filament\Resources\CommunityReviews\CommunityReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCommunityReviews extends ListRecords
{
    protected static string $resource = CommunityReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
