<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Str;

/**
 * 카테고리 관리를 위한 Filament 리소스 클래스
 * 상품 카테고리의 CRUD 작업과 관리자 인터페이스를 제공
 */
class CategoryResource extends Resource
{
    /** @var string 연결된 모델 클래스 */
    protected static ?string $model = Category::class;

    /** @var string 네비게이션 아이콘 */
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    
    /** @var int 네비게이션 메뉴 정렬 순서 */
    protected static ?int $navigationSort = 3;

    /**
     * 카테고리 생성/수정 폼 구성
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
                        TextInput::make('name')  // 카테고리명 입력 필드
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(  // 카테고리명 입력 시 자동으로 slug 생성
                                fn(string $operation, $state, Set $set) => 
                                    $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        TextInput::make('slug')  // URL 식별자 필드
                            ->maxLength(255)
                            ->required()
                            ->dehydrated()
                            ->unique(Category::class, 'slug', ignoreRecord: true)
                    ]),
                    FileUpload::make('image')  // 카테고리 이미지 업로드
                        ->image()
                        ->directory('categories'),
                    Toggle::make('is_active')  // 활성화 상태 토글
                        ->required()
                        ->default(true)
                ])
            ]);
    }

    /**
     * 카테고리 목록 테이블 구성
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')  // 카테고리명 컬럼
                    ->searchable(),
                ImageColumn::make('image')  // 이미지 컬럼
                    ->searchable(),
                TextColumn::make('slug')  // URL 식별자 컬럼
                    ->searchable(),
                IconColumn::make('is_active')  // 활성화 상태 컬럼
                    ->boolean(),
                TextColumn::make('created_at')  // 생성일시 컬럼
                    ->dateTime('Y년 m월 d일 H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')  // 수정일시 컬럼
                    ->dateTime('Y년 m월 d일 H:i:s')
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
            'index' => Pages\ListCategories::route('/'),          // 목록 페이지
            'create' => Pages\CreateCategory::route('/create'),   // 생성 페이지
            'edit' => Pages\EditCategory::route('/{record}/edit'),  // 수정 페이지
        ];
    }
}
