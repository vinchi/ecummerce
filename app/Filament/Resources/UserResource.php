<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * 사용자 관리를 위한 Filament 리소스 클래스
 * 사용자의 CRUD 작업과 관리자 인터페이스를 제공
 */
class UserResource extends Resource
{
    /** @var string 연결된 모델 클래스 */
    protected static ?string $model = User::class;

    /** @var string 네비게이션 아이콘 */
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    /** @var string 레코드 제목에 사용될 속성 */
    protected static ?string $recordTitleAttribute = 'name';
    
    /** @var int 네비게이션 메뉴 정렬 순서 */
    protected static ?int $navigationSort = 1;

    /**
     * 사용자 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')  // 사용자 이름 입력 필드
                    ->required(),
                TextInput::make('email')  // 이메일 입력 필드
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)  // 중복 이메일 방지
                    ->required(),
                DateTimePicker::make('email_verified_at')  // 이메일 인증 날짜 입력 필드
                    ->label('Email Verified At')
                    ->default(now()),  // 기본값 현재 시간
                TextInput::make('password')  // 비밀번호 입력 필드
                    ->password()
                    ->dehydrated(fn($state) => filled($state))  // 비밀번호가 입력된 경우에만 저장
                    ->required(fn(Page $livewire): bool => $livewire instanceof CreateRecord),  // 생성 시 필수
            ]);
    }

    /**
     * 사용자 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')  // 사용자 이름 컬럼
                    ->searchable(),
                TextColumn::make('email')  // 이메일 컬럼
                    ->searchable(),
                TextColumn::make('email_verified_at')  // 이메일 인증 날짜 컬럼
                    ->dateTime('Y년 m월 d일 H:i:s')  // 날짜 형식 설정
                    ->sortable(),
                TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime('Y년 m월 d일 H:i:s')  // 날짜 형식 설정
                    ->sortable(),
            ])
            ->filters([
                // 필터 설정
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([  // 레코드별 액션 그룹
                    Tables\Actions\EditAction::make(),    // 수정 액션
                    Tables\Actions\ViewAction::make(),    // 조회 액션
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
            OrdersRelationManager::class  // 주문 관계 관리자
        ];
    }
    
    /**
     * 전역 검색 가능한 속성 설정
     * 
     * @return array
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];  // 검색 가능한 속성
    }

    /**
     * 리소스 페이지 설정
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),          // 목록 페이지
            'create' => Pages\CreateUser::route('/create'),  // 생성 페이지
            'edit' => Pages\EditUser::route('/{record}/edit'),  // 수정 페이지
        ];
    }
}
