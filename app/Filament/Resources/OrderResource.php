<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\AddressRelationManager;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;

/**
 * 주문 관리를 위한 Filament 리소스 클래스
 * 주문의 CRUD 작업과 관리자 인터페이스를 제공
 */
class OrderResource extends Resource
{
    /** @var string 연결된 모델 클래스 */
    protected static ?string $model = Order::class;

    /** @var string 네비게이션 아이콘 */
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    /** @var int 네비게이션 메뉴 정렬 순서 */
    protected static ?int $navigationSort = 5;
    
    /**
     * 주문 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Order Information')->schema([  // 주문 기본 정보 섹션
                        Select::make('user_id')  // 고객 선택
                            ->label('Customer')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Select::make('payment_method')  // 결제 방법
                            ->options([
                                'stripe' => 'Stripe',
                                'cod' => 'Cash on Delivery'
                            ])
                            ->required(),
                            
                        Select::make('payment_status')  // 결제 상태
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed'
                            ])
                            ->default('pending')
                            ->required(),
                            
                        ToggleButtons::make('status')  // 주문 상태
                            ->inline()
                            ->default('new')
                            ->required()
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'candcelled' => 'Cancelled',
                            ])
                            ->colors([  // 상태별 색상
                                'new' => 'info',
                                'processing' => 'warning',
                                'shipped' => 'success',
                                'delivered' => 'success',
                                'cancelled' => 'danger',
                            ])
                            ->icons([  // 상태별 아이콘
                                'new' => 'heroicon-m-sparkles',
                                'processing' => 'heroicon-m-arrow-path',
                                'shipped' => 'heroicon-m-truck',
                                'delivered' => 'heroicon-m-check-badge',
                                'cancelled' => 'heroicon-m-x-circle',
                            ]),
                        
                        Select::make('currency')  // 통화 단위
                            ->options([
                                'krw' => 'KRW',
                                'usd' => 'USD',
                                'inr' => 'INR',
                                'eur' => 'EUR',
                                'gbp' => 'GBP',
                            ])
                            ->default('krw')
                            ->required(),
                        
                        Select::make('shipping_method')  // 배송 방법
                            ->options([
                                'fedex' => 'FedEx',
                                'ups' => 'UPS',
                                'dhl' => 'DHL',
                                'usps' => 'USPS',
                            ]),
                            
                        Textarea::make('notes')  // 주문 메모
                            ->columnSpanFull()
                            
                    ])->columns(2),
                    
                    Section::make('Order Items')->schema([  // 주문 상품 목록 섹션
                        Repeater::make('items')  // 주문 상품 반복 입력
                            ->relationship()
                            ->schema([
                                Select::make('product_id')  // 상품 선택
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(4)
                                    ->reactive()
                                    ->afterStateUpdated(  // 상품 선택 시 단가 자동 설정
                                        fn($state, Set $set) => $set('unit_amount', Product::find($state)?->price ?? 0)
                                    )
                                    ->afterStateUpdated(  // 총액 자동 계산
                                        fn($state, Set $set) => $set('total_amount', Product::find($state)?->price ?? 0)
                                    ),
                                    
                                TextInput::make('quantity')  // 수량 입력
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(  // 수량 변경 시 총액 자동 계산
                                        fn($state, Set $set, Get $get) => $set('total_amount', $state * $get('unit_amount'))
                                    ),
                                
                                TextInput::make('unit_amount')  // 단가
                                    ->numeric()
                                    ->required()
                                    ->disabled()
                                    ->columnSpan(3),  
                                
                                TextInput::make('total_amount')  // 항목별 총액
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(3),  
                            ])->columns(12),
                            
                        Placeholder::make('grand_total_placeholder')  // 전체 총액 표시
                            ->label('Grand Total')
                            ->content(function(Get $get, Set $set) {
                                $total = 0;
                                if(!$repeaters = $get('items')) {
                                    return $total;
                                }
                                foreach($repeaters as $key => $repeater) {
                                    $total += $get("items.{$key}.total_amount");
                                }
                                
                                $set('grand_total', $total);
                                
                                return Number::currency($total, 'KRW');
                            }),
                                
                        Hidden::make('grand_total')  // 총액 hidden 필드
                            ->default(0),
                    ])                  
                ])->columnSpanFull(),
            ]);
    }

    /**
     * 주문 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')  // 고객명
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('grand_total')  // 총액
                    ->numeric()
                    ->sortable()
                    ->money('KRW'),
                    
                TextColumn::make('payment_method')  // 결제 방법
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('payment_status')  // 결제 상태
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('currency')  // 통화
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('shipping_method')  // 배송 방법
                    ->searchable()
                    ->sortable(),
                    
                SelectColumn::make('status')  // 주문 상태
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'candcelled' => 'Cancelled',
                    ])
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // 필터 설정
            ])
            ->actions([
                ActionGroup::make([  // 레코드별 액션 그룹
                    Tables\Actions\ViewAction::make(),    // 조회
                    Tables\Actions\EditAction::make(),    // 수정
                    Tables\Actions\DeleteAction::make(),  // 삭제
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
            AddressRelationManager::class  // 주소 관계 관리자
        ];
    }
    
    /**
     * 네비게이션 배지 표시 설정
     * 전체 주문 수를 표시
     * 
     * @return string|null
     */
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    /**
     * 네비게이션 배지 색상 설정
     * 주문 수에 따라 색상 변경
     * 
     * @return string|array|null
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    /**
     * 리소스 페이지 설정
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),          // 목록 페이지
            'create' => Pages\CreateOrder::route('/create'),  // 생성 페이지
            'view' => Pages\ViewOrder::route('/{record}'),    // 상세 페이지
            'edit' => Pages\EditOrder::route('/{record}/edit'),  // 수정 페이지
        ];
    }
}
