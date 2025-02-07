<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * 브랜드 관리를 위한 Filament 리소스 클래스
 * 브랜드의 CRUD 작업과 관리자 인터페이스를 제공
 */
class BrandResource extends Resource
{
    /** @var string 연결된 모델 클래스 */
    protected static ?string $model = Brand::class;

    /** @var string 네비게이션 아이콘 */
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    
    /** @var string 레코드 제목에 사용될 속성 */
    protected static ?string $recordTitleAttribute = 'name';
    
    /** @var int 네비게이션 메뉴 정렬 순서 */
    protected static ?int $navigationSort = 2;

    /**
     * 브랜드 생성/수정 폼 구성
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Grid::make()->schema([
                        Forms\Components\TextInput::make('name')  // 브랜드명 입력 필드
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(  // 브랜드명 입력 시 자동으로 slug 생성
                                fn(string $operation, $state, Forms\Set $set) => 
                                    $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null
                            ),
                            
                        Forms\Components\TextInput::make('slug')  // URL 식별자 필드
                            ->maxLength(255)
                            ->disabled()
                            ->required()
                            ->dehydrated()
                            ->unique(Brand::class, 'slug', ignoreRecord: true),
                    ]),
                    Forms\Components\FileUpload::make('image')  // 브랜드 이미지 업로드
                            ->image()
                            ->directory('brands'),
                            
                    Forms\Components\Toggle::make('is_active')  // 활성화 상태 토글
                        ->required()
                        ->default(true),
                ])
            ]);
    }

    /**
     * 브랜드 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')  // 브랜드명 컬럼
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')  // URL 식별자 컬럼
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),  // 이미지 컬럼
                Tables\Columns\IconColumn::make('is_active')  // 활성화 상태 컬럼
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')  // 수정일시 컬럼
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            // 관계 관리자 설정
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
            'index' => Pages\ListBrands::route('/'),          // 목록 페이지
            'create' => Pages\CreateBrand::route('/create'),  // 생성 페이지
            'edit' => Pages\EditBrand::route('/{record}/edit'),  // 수정 페이지
        ];
    }
}
