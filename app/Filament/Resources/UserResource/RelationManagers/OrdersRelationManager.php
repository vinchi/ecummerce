<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * 사용자와 관련된 주문 관리를 위한 관계 관리자 클래스
 * 사용자의 주문 목록을 관리하고 표시하는 기능을 제공
 */
class OrdersRelationManager extends RelationManager
{
    /** @var string 연결된 관계 이름 */
    protected static string $relationship = 'orders';

    /**
     * 주문 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')  // 주문 ID 입력 필드
                    ->required()
                    ->maxLength(255),
            ]);
    }

    /**
     * 주문 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')  // 레코드 제목 속성 설정
            ->columns([
                TextColumn::make('id')  // 주문 ID 컬럼
                    ->label('Order ID')
                    ->searchable(),
                    
                TextColumn::make('grand_total')  // 총 금액 컬럼
                    ->money("KRW"),

                TextColumn::make('status')  // 주문 상태 컬럼
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'new' => 'heroicon-m-sparkles',
                        'processing' => 'heroicon-m-arrow-path',
                        'shipped' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-badge',
                        'cancelled' => 'heroicon-m-x-circle',
                    })
                    ->sortable(),
                    
                TextColumn::make('payment_method')  // 결제 방법 컬럼
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('payment_status')  // 결제 상태 컬럼
                    ->sortable()
                    ->badge()
                    ->searchable(),
                    
                TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // 필터 설정
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),  // 주문 생성 액션
            ])
            ->actions([
                Action::make('View Order')  // 주문 조회 액션
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),  // 주문 삭제 액션
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // 일괄 삭제 액션
                ]),
            ]);
    }
}
