<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Enums\ProductVariationTypeEnum;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $title = 'Variations';

    public function form(Form $form): Form
    {
        $types = $this->record->variationTypes;
        $fields = [];
        foreach ($types as $type) {
            $fields[] = TextInput::make('variation_type_' . $type->id . '.id')
                ->hidden();

            $fields[] = TextInput::make('variation_type_' . $type->id . '.name')
                ->label($type->name);
        }
        return $form->schema([
            Repeater::make('variations')
                ->addable(false)
                ->label(false)
            ->collapsible()
            ->defaultItems(1)
            ->schema([
                Section::make()
                  ->schema($fields)
                  ->columns(3),
                TextInput::make('quantity')
                ->label('Quantity')
                ->numeric(),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
            ])
            ->columnSpan(2)
            ->columns(2)
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variations);
        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $data): array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;
        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergeResult = [];
        foreach ($cartesianProduct as $i => $product) {
            $optionIds = collect($product)
                ->filter(fn($value, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn($option) => $option['id'])
                ->values()
                ->toArray();

            $match= array_filter($data, function($options) use ($optionIds) {
                return $options['variation_type_option_ids'] == $optionIds;
            });


            if(!empty($match)){
                $existingEntry = reset($match);
                $product['id'] = $existingEntry['id'];
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            }
            else{
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }

            $mergeResult[] = $product;
        }


        return $mergeResult;
    }

    private function cartesianProduct($variationTypes, mixed $defaultQuantity=null, mixed $defaultPrice=null)
    {
        $result = [[]];
        foreach ($variationTypes as $index => $variationType) {
            $temp = [];
            foreach ($variationType['options'] as $option) {
                foreach($result as $combination){
                    $newCombination = $combination + [
                            'variation_type_' . ($variationType->id) => [
                                'option_id' => $option->id,
                                'name' => $option->name,
                                'type' => $variationType->type,
                                'id' => $option->id,
                            ]
                        ];

                    $temp[] = $newCombination;
                }
            }
            $result = $temp;
        }

        foreach($result as $combination){
            if(count($combination)  === count($variationTypes)){
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
            }
        }

        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $formattedData = [];

        foreach ($data['variations'] as $option) {
            $variationTypeOptionIds = [];
            foreach ($this->record->variationTypes as $i => $variationType) {
                $variationTypeOptionIds[] = $option['variation_type_'.($variationType->id)]['option_id'];
            }

            $quantity = $option['quantity'];
            $price = $option['price'];
            $formattedData[] = [
                'id' => $option['id']??null,
              'variation_type_option_ids' => $variationTypeOptionIds,
              'quantity' => $quantity,
              'price' => $price,
            ];
        }
        $data['variations'] = $formattedData;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variations = $data['variations'];
        unset($data['variations']);

        $variations = collect($variations)->map(function ($variation) {
            return [
                'id' => $variation['id'],
                'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                'quantity' => $variation['quantity'],
                'price' => $variation['price'],
            ];
        })->toArray();


        $record->variations()->upsert($variations, ['id'], ['quantity', 'variation_type_option_ids', 'price']);

        return $record;
    }
}
