<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Address;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

/**
 * 주문과 관련된 주소 관리를 위한 관계 관리자 클래스
 * 사용자의 주소 목록을 관리하고 표시하는 기능을 제공
 */
class AddressRelationManager extends RelationManager
{
    /** @var string 연결된 관계 이름 */
    protected static string $relationship = 'address';

    /**
     * 주소 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('street_address')  // 거리 주소 입력 필드
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('city')  // 도시 입력 필드
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('state')  // 주/도 입력 필드
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('zip_code')  // 우편번호 입력 필드
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('country')  // 국가 입력 필드
                    ->required()
                    ->maxLength(100),
            ]);
    }

    /**
     * 주소 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('street_address')  // 레코드 제목 속성 설정
            ->columns([
                TextColumn::make('street_address')  // 거리 주소 컬럼
                    ->label('Street Address')
                    ->searchable(),
                    
                TextColumn::make('city')  // 도시 컬럼
                    ->searchable(),
                    
                TextColumn::make('state')  // 주/도 컬럼
                    ->searchable(),
                    
                TextColumn::make('zip_code')  // 우편번호 컬럼
                    ->searchable(),
                    
                TextColumn::make('country')  // 국가 컬럼
                    ->searchable(),
                    
                TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // 필터 설정
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),  // 주소 생성 액션
            ])
            ->actions([
                Action::make('View Address')  // 주소 조회 액션
                    ->url(fn (Address $record): string => route('addresses.view', ['record' => $record]))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),  // 주소 삭제 액션
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // 일괄 삭제 액션
                ]),
            ]);
    }
}
