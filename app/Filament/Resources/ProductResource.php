<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Brand;
use App\Models\Product;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * 상품 관리를 위한 Filament 리소스 클래스
 * 상품의 CRUD 작업과 관리자 인터페이스를 제공
 */
class ProductResource extends Resource
{
    /** @var string 연결된 모델 클래스 */
    protected static ?string $model = Product::class;

    /** @var string 네비게이션 아이콘 */
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    
    /** @var int 네비게이션 메뉴 정렬 순서 */
    protected static ?int $navigationSort = 4;

    /**
     * 상품 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Product Information')->schema([  // 상품 기본 정보 섹션
                        TextInput::make('name')  // 상품명 입력 필드
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(  // 상품명 입력 시 자동으로 slug 생성
                                function(string $operation, $state, Set $set) {
                                    if($operation !== 'create') {
                                        return;
                                    }
                                    $set('slug', Str::slug($state));
                                }
                            ),
                            
                        TextInput::make('slug')  // URL 식별자 필드
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                            
                        MarkdownEditor::make('description')  // 상품 설명 입력 필드
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('products'),
                        
                    ])->columns(2),
                    Section::make('Images')->schema([  // 이미지 업로드 섹션
                        FileUpload::make('images')  // 상품 이미지 업로드
                            ->multiple()
                            ->directory('products')
                            ->maxFiles(5)
                            ->reorderable()
                    ])
                ])->columnSpan(2),
                Group::make()->schema([
                    TextInput::make('price')  // 상품 가격 입력 필드
                        ->numeric()
                        ->required()
                        ->prefix('WON'),
                        
                    Section::make('Associations')->schema([  // 연관 정보 섹션
                        Select::make('category_id')  // 카테고리 선택
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('category', 'name'),
                        
                        Select::make('brand_id')  // 브랜드 선택
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('brand', 'name'),
                    ]),
                    
                    Section::make('Status')->schema([  // 상태 설정 섹션
                        Toggle::make('in_stock')  // 재고 여부 토글
                            ->required()
                            ->default(true),
                        
                        Toggle::make('is_active')  // 활성화 상태 토글
                            ->required()
                            ->default(true),
                            
                        Toggle::make('is_featured')  // 추천 상품 여부 토글
                            ->required(),
                            
                        Toggle::make('is_sale')  // 세일 여부 토글
                            ->required()
                    ])
                ])->columnSpan(1)
            ])->columns(3);
    }

    /**
     * 상품 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')  // 상품명 컬럼
                    ->searchable(),
                TextColumn::make('category.name')  // 카테고리명 컬럼
                    ->sortable(),
                TextColumn::make('brand.name')  // 브랜드명 컬럼
                    ->sortable(),
                TextColumn::make('price')  // 가격 컬럼
                    ->money('WON')
                    ->sortable(),
                IconColumn::make('is_featured')  // 추천 상품 여부 컬럼
                    ->boolean(),
                IconColumn::make('is_sale')  // 세일 여부 컬럼
                    ->boolean(),
                IconColumn::make('in_stock')  // 재고 여부 컬럼
                    ->boolean(),
                IconColumn::make('is_active')  // 활성화 상태 컬럼
                    ->boolean(),
                TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')  // 수정일시 컬럼
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
            ])
            ->filters([
                SelectFilter::make('category')  // 카테고리 필터
                    ->relationship('category', 'name'),
                SelectFilter::make('brand')  // 브랜드 필터
                    ->relationship('brand', 'name')
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([  // 레코드별 액션 그룹
                    Tables\Actions\ViewAction::make(),    // 조회 액션
                    Tables\Actions\EditAction::make(),    // 수정 액션
                    Tables\Actions\DeleteAction::make()   // 삭제 액션
                ])
            ])
            ->bulkActions([  // 일괄 처리 액션
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // 일괄 삭제
                ]),
            ]);
    }

    /**
     * 관계 관리자 설정
     * 
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * 리소스 페이지 설정
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),          // 목록 페이지
            'create' => Pages\CreateProduct::route('/create'),  // 생성 페이지
            'edit' => Pages\EditProduct::route('/{record}/edit'),  // 수정 페이지
        ];
    }
    
    /**
     * 주문 후 처리 메소드
     * 
     * @param Brand $brand
     * @return void
     */
    public static function afterSave(Brand $brand): void {
        
    }
}
