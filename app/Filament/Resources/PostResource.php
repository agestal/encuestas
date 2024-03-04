<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Models\Tag;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'eos-article';

    protected static ?int $navigationStart = 20;

    public static function getNavigationGroup(): ?string
    {
        return __('Blog');
    }
    public static function getLabel(): ?string
    {
        return __('Post');
    }
    public static function getNavigationLabel(): string
    {
        return __('Posts');
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(
            ! auth()->user()->hasRole('super_admin'),
            fn( Builder $query ) => $query->where('user_id', auth()->user()->id ),
        );
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->columns(3)
                ->schema([
                    Forms\Components\Section::make()
                        ->columnSpan(2)
                        ->schema([
                            Forms\Components\TextInput::make('titulo')
                                ->label(__('Titulo'))
                                ->live(onBlur: true)
                                ->required()
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                ->maxLength(255),
                            Forms\Components\TextInput::make('slug')
                                ->label(__('Slug'))
                                ->required()
                                ->disabled()
                                ->dehydrated()
                                ->unique(Post::class, 'slug', ignoreRecord: true)
                                ->maxLength(255),
                            Forms\Components\MarkdownEditor::make('extracto')
                                ->required()
                                ->label(__('Extracto'))
                                ->columnSpanFull(),
                            Forms\Components\MarkdownEditor::make('contenido')
                                ->label(__('Contenido'))
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\Select::make('user_id')
                                ->relationship('autor','name')
                                ->label(__('Autor'))
                                //->disabled(fn() => auth()->user()->hasRole('Autor') || ! auth()->user()->hasAnyRole() )
                                ->default( fn() => auth()->id())
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('categoria_id')
                                ->hidden(fn() => auth()->user()->can('create_categoria') )
                                ->relationship('categoria', 'nombre')
                                ->label(__('Categoria'))
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('categoria_id')
                                ->hidden(fn() => ! auth()->user()->can('create_categoria') )
                                ->relationship('categoria', 'nombre')
                                ->label(__('Categoria'))
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nombre')
                                        ->required()
                                        ->label(__('Nombre'))
                                        ->unique(static::getModel(), 'nombre', ignoreRecord:true)
                                        ->live(debounce:500)
                                        ->autofocus()
                                        ->maxLength(255)
                                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                                    Forms\Components\TextInput::make('slug')
                                        ->label(__('Slug'))
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->searchable()
                                ->required(),
                            Forms\Components\Select::make('tags')
                                ->relationship('tags', 'nombre')
                                ->label(__('Tags'))
                                ->searchable()
                                ->multiple()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nombre')
                                        ->required()
                                        ->label(__('Nombre'))
                                        ->unique(Tag::class, 'nombre', ignoreRecord: true)
                                        ->autofocus()
                                        ->maxLength(255),
                                ]),
                        ]),
                    Forms\Components\Section::make()
                        ->columnSpan(1)
                        ->schema([
                            Forms\Components\FileUpload::make('imagen')
                                ->label(__('Imagen'))
                                ->image()
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('publicado')
                                ->label(__('Publicado'))
                                ->required(),
                            Forms\Components\DateTimePicker::make('publicado_en')
                                ->label(__('Publicado en'))
                                ->default(false),
                            Forms\Components\Placeholder::make('created_at')
                                ->label(__('Creado'))
                                ->content(fn(Post $record) : ?string => $record->created_at?->diffForHumans() )
                                ->hidden(fn(?Post $record) => $record === null ),
                            Forms\Components\Placeholder::make('updated_at')
                                ->label(__('Actualizado'))
                                ->content(fn(Post $record) : ?string => $record->created_at?->diffForHumans() )
                                ->hidden(fn(?Post $record) => $record === null ),
                            
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('imagen')
                    ->label(__('Imagen')),
                Tables\Columns\TextColumn::make('categoria.nombre')
                    ->label(__('Nombre'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('autor.name')
                    ->label(__('Nombre'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('titulo')
                    ->label(__('Titulo'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('tags.nombre')
                    ->label(__('Tags'))
                    ->searchable()
                    ->badge(),
                Tables\Columns\ToggleColumn::make('publicado')
                    ->label(__('Publicado')),
                Tables\Columns\TextColumn::make('publicado_en')
                    ->label(__('Publicado en'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
