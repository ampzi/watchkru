<?php

namespace App\Filament\Resources\CommunityReviews\Pages;

use App\Filament\Resources\CommunityReviews\CommunityReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommunityReview extends EditRecord
{
    protected static string $resource = CommunityReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
