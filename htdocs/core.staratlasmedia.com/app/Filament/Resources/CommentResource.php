<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommentResource\Pages\ManageComments;
use App\Models\Comment;
use App\Models\CommentModerationEvent;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use UnitEnum;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleOvalLeftEllipsis;

    protected static ?string $navigationLabel = 'Comments';

    protected static string|UnitEnum|null $navigationGroup = 'Comments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('site_id')->relationship('site', 'name')->searchable()->preload()->required(),
            Select::make('comment_thread_id')->relationship('thread', 'source_url')->searchable()->preload(),
            Select::make('parent_id')->relationship('parent', 'uuid')->searchable()->preload(),
            TextInput::make('author_display_name'),
            Textarea::make('body')->required()->rows(6),
            Select::make('status')
                ->options(self::statusOptions())
                ->required(),
            TextInput::make('source_url')->url(),
            Textarea::make('metadata_json')->json()->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.code')->label('Site')->searchable()->sortable(),
                TextColumn::make('thread.source_title')->label('Thread')->searchable()->limit(32),
                TextColumn::make('author_display_name')->label('Author')->searchable(),
                TextColumn::make('body')->limit(64)->searchable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('depth')->sortable(),
                TextColumn::make('replies_count')->label('Replies')->sortable(),
                TextColumn::make('likes_count')->label('Likes')->sortable(),
                TextColumn::make('reports_count')->label('Reports')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->recordActions([
                self::moderationAction('approve', Comment::STATUS_APPROVED, CommentModerationEvent::TYPE_APPROVED),
                self::moderationAction('reject', Comment::STATUS_REJECTED, CommentModerationEvent::TYPE_REJECTED),
                self::moderationAction('spam', Comment::STATUS_SPAM, CommentModerationEvent::TYPE_MARKED_SPAM),
                self::moderationAction('trash', Comment::STATUS_TRASH, CommentModerationEvent::TYPE_TRASHED),
                self::moderationAction('restore', Comment::STATUS_PENDING, CommentModerationEvent::TYPE_RESTORED),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')->action(fn (Collection $records) => self::moderateMany($records, Comment::STATUS_APPROVED, CommentModerationEvent::TYPE_APPROVED)),
                    BulkAction::make('reject')->action(fn (Collection $records) => self::moderateMany($records, Comment::STATUS_REJECTED, CommentModerationEvent::TYPE_REJECTED)),
                    BulkAction::make('markSpam')->label('Mark spam')->action(fn (Collection $records) => self::moderateMany($records, Comment::STATUS_SPAM, CommentModerationEvent::TYPE_MARKED_SPAM)),
                    BulkAction::make('trash')->action(fn (Collection $records) => self::moderateMany($records, Comment::STATUS_TRASH, CommentModerationEvent::TYPE_TRASHED)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageComments::route('/'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function statusOptions(): array
    {
        return [
            Comment::STATUS_PENDING => 'Pending',
            Comment::STATUS_APPROVED => 'Approved',
            Comment::STATUS_REJECTED => 'Rejected',
            Comment::STATUS_SPAM => 'Spam',
            Comment::STATUS_TRASH => 'Trash',
            Comment::STATUS_DELETED => 'Deleted',
        ];
    }

    private static function moderationAction(string $name, string $status, string $eventType): Action
    {
        return Action::make($name)
            ->requiresConfirmation()
            ->action(fn (Comment $record): bool => self::moderate($record, $status, $eventType));
    }

    private static function moderate(Comment $comment, string $status, string $eventType): bool
    {
        $oldStatus = $comment->status;
        $comment->forceFill([
            'status' => $status,
            'approved_at' => $status === Comment::STATUS_APPROVED ? now() : $comment->approved_at,
            'rejected_at' => in_array($status, [Comment::STATUS_REJECTED, Comment::STATUS_SPAM], true) ? now() : $comment->rejected_at,
            'trashed_at' => $status === Comment::STATUS_TRASH ? now() : $comment->trashed_at,
        ])->save();
        $comment->moderationEvents()->create([
            'site_id' => $comment->site_id,
            'event_type' => $eventType,
            'old_status' => $oldStatus,
            'new_status' => $status,
            'metadata_json' => ['source' => 'filament'],
        ]);

        return true;
    }

    private static function moderateMany(Collection $records, string $status, string $eventType): void
    {
        $records->each(fn (Comment $comment): bool => self::moderate($comment, $status, $eventType));
    }
}
