<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\UmkmOwner;
use Filament\Tables\Table;
use App\Models\UmkmProduct;
use App\Models\ProductCategory;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\CheckboxColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UmkmProductResource\Pages;
use App\Filament\Resources\UmkmProductResource\RelationManagers;

class UmkmProductResource extends Resource
{
    protected static ?string $model = UmkmProduct::class;

    protected static ?string $navigationGroup = 'UMKM';
    protected static ?string $navigationLabel = 'Umkm Product List';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                //
                Section::make('Umkm Product Information')
                    ->columnSpan(2)
                    ->schema([
                        hidden::make('umkm_owner_id')
                            ->default(auth()->user()->umkmOwner->id),

                        TextInput::make('product_name')
                            ->label('Product Name')
                            ->required()
                            ->placeholder('Enter the name of the product'),

                        Select::make('product_category_id')
                            ->label('Product Category')
                            ->options(
                                ProductCategory::all()->pluck('category_name', 'id')
                            )
                            ->searchable()
                            ->required(),   

                        FileUpload::make('product_image')
                            ->disk('public')
                            ->directory('product-thumbnails')
                            ->label('Product Image')
                            ->image()
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(1024)
                            ->imageEditor(),

                        TextInput::make('product_price')
                            ->label('Product Price')
                            ->required()
                            ->numeric()
                            ->required(),

                        RichEditor::make('product_description')
                            ->label('Product Description')
                            ->placeholder('Enter the description of the product')
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('product-thumbnails')
                            ->columnSpanFull()
                            ->required(),

                        FileUpload::make('product_gallery')
                            ->label('Product Gallery')
                            ->multiple()
                            ->image()
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(1024)
                            ->imageEditor(),
                        ]),
                        
                Section::make('Meta')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('product_location')
                            ->label('Product Location')
                            ->required()
                            ->placeholder('Enter the location of the product'),

                        TextInput::make('product_social_media')
                        ->label('Product Social Media'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->placeholder('Enter the slug of the blog'),

                        Textarea::make('tags')
                            ->label('Tags')
                            ->columnSpanFull(),
                            
                        CheckBox::make('is_published')
                            ->label('Is Published')
                            ->required(),           
                    ]),
            ])->columns(3);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('umkmOwner.id')
                    ->label('Owner Id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                ImageColumn::make('product_image'),
                ImageColumn::make('product_gallery'),
                TextColumn::make('product_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_category_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_location')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->toggleable()
                    ->date()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin')) {
            return parent::getEloquentQuery();
        } else {
            $umkmOwnerId = $user->umkmOwner->id;
            return parent::getEloquentQuery()->where('umkm_owner_id', $umkmOwnerId);
        }
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUmkmProducts::route('/'),
            'create' => Pages\CreateUmkmProduct::route('/create'),
            'edit' => Pages\EditUmkmProduct::route('/{record}/edit'),
        ];
    }
}
