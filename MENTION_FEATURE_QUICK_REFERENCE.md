# @Mention Feature - Quick Reference Guide

## Feature Overview
Users can mention other active users in ticket comments using `@username` syntax. Mentioned users receive notifications.

## How It Works (User Perspective)

1. **Open a ticket** → Scroll to "Comments" section
2. **Type `@` in comment box** → Autocomplete dropdown appears
3. **Type username** → Results filter in real-time
4. **Arrow keys** to navigate, **Enter** to select
5. **Escape** to close dropdown
6. **Post comment** → Mentioned users get notifications

## How It Works (Developer Perspective)

### Data Flow
```
User types "@John" 
  ↓
Frontend: Alpine.js detectMention() fires on keyup
  ↓
Frontend: Debounced AJAX to /api/users/search?q=John
  ↓
Backend: UserController@search() returns active users
  ↓
Frontend: Dropdown shows results, user selects
  ↓
User submits comment form
  ↓
Backend: TicketController@storeComment() processes
  ↓
extractMentionedUsers() parses @mentions with regex
  ↓
DB Transaction: Create comment + CommentMention records
  ↓
Send TicketNotification('mentioned', ...) to mentioned users
  ↓
Mentioned users see notification in bell icon
```

## Key Files

| File | Purpose | Lines |
|------|---------|-------|
| `app/Http/Controllers/TicketController.php` | Stores comment, extracts mentions, sends notifications | 742-865 |
| `app/Http/Controllers/UserController.php` | AJAX search endpoint for mention autocomplete | 128-152 |
| `app/Models/TicketComment.php` | Mention relations | 36-51 |
| `app/Models/CommentMention.php` | Tracks mentions | 1-28 |
| `app/Notifications/TicketNotification.php` | 'mentioned' event | 60-64 |
| `resources/views/tickets/show.blade.php` | Frontend: autocomplete + highlight | 418-429, 679-893 |
| `database/migrations/...create_comment_mentions_table` | DB schema | 1-36 |
| `tests/Feature/MentionNotificationTest.php` | 11 test cases | 1-400+ |

## Code Locations

### Backend: Extracting Mentions
```php
// File: app/Http/Controllers/TicketController.php:847-865
private function extractMentionedUsers(string $commentText): array
{
    // Regex: /@([a-zA-Z0-9_\-\.]+)/
    // Returns: ['user_id' => 1, ...] (deduplicated)
}
```

### Backend: Storing Mentions
```php
// File: app/Http/Controllers/TicketController.php:758-778
DB::transaction(function () {
    // Create comment
    // For each mentioned user, create CommentMention record
    // Unique constraint (comment_id, user_id) prevents duplicates
});
```

### Backend: Sending Notifications
```php
// File: app/Http/Controllers/TicketController.php:819-826
if (!empty($mentionedUserIds)) {
    $mentionedUsers = User::whereIn('id', array_keys($mentionedUserIds))
        ->where('is_active', true)->get();
    foreach ($mentionedUsers as $mentionedUser) {
        if ($mentionedUser->id !== $user->id) {  // Prevent self-mention
            $mentionedUser->notify(new TicketNotification('mentioned', $ticket, $user->name));
        }
    }
}
```

### Frontend: Autocomplete Detection
```javascript
// File: resources/views/tickets/show.blade.php:753-798
detectMention() {
    // Look for @ backwards from cursor
    // Extract text after @
    // Validate (only word chars, no spaces)
    // Fetch users from API if query changed
}
```

### Frontend: Mention Highlighting
```blade
// File: resources/views/tickets/show.blade.php:418-429
@php
    $displayText = e($comment->comment);
    $mentions = $comment->mentions()->pluck('mentioned_name')->unique();
    foreach ($mentions as $mentionedName) {
        // Replace @name with <span class="highlight">@name</span>
    }
@endphp
{!! $displayText !!}
```

## Database Schema

```sql
CREATE TABLE comment_mentions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    comment_id BIGINT FOREIGN KEY → ticket_comments(id),
    user_id BIGINT FOREIGN KEY → users(id),
    mentioned_name VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY (comment_id, user_id),
    INDEX (user_id)
);
```

## API Endpoints

### User Search (for autocomplete)
```
GET /api/users/search?q=john
Response: [
    { id: 1, name: "John Doe", email: "john@example.com" },
    { id: 5, name: "Johnny Smith", email: "johnny@example.com" }
]
```

### Store Comment (with mentions)
```
POST /my-tickets/{ticket_id}/comments
Body: { comment: "Hey @John, check this", attachments: [] }
```

## Regex Pattern

```
/@([a-zA-Z0-9_\-\.]+)/
```

**Matches:**
- `@john` ✅
- `@john_doe` ✅
- `@john-smith` ✅
- `@john.smith` ✅
- `@JOHN` ✅

**Doesn't match:**
- `@ john` (space after @) ❌
- `@john smith` (space in middle) ❌
- `@john!` (special char) ❌

## Unique Constraint

The database enforces **one mention per user per comment**:

```sql
UNIQUE KEY (comment_id, user_id)
```

If you try to insert duplicate:
```
Comment ID 5, User ID 3 → first time ✅
Comment ID 5, User ID 3 → second time ❌ (duplicate)
```

This happens automatically in `extractMentionedUsers()`:
```php
$mentionedNames = array_unique($matches[1]); // Remove duplicates from text
// Then database unique constraint prevents duplicate inserts
```

## Prevention Mechanisms

### Self-Mention Prevention
```php
if ($mentionedUser->id !== $user->id) {
    $mentionedUser->notify(...);  // Only notify if different user
}
```

### Inactive User Prevention
```php
$mentionedUsers = User::where('is_active', true)
    ->whereIn('name', $mentionedNames)
    ->get();
```

### Spam Prevention
- Debounced AJAX (250ms)
- AbortController cancels old requests
- Dropdown limited to 10 results
- Unique constraint prevents duplicates

## Testing

Run tests:
```bash
php artisan test tests/Feature/MentionNotificationTest.php
```

Test cases cover:
1. Mention creates notification ✅
2. Self-mention prevented ✅
3. Duplicate mention prevented ✅
4. Inactive user ignored ✅
5. Regular comments work ✅
6. Existing comments display ✅
7. Search endpoint works ✅
8. Multiple mentions work ✅
9. Works with attachments ✅
10. Mention data stored correctly ✅
11. XSS prevention ✅

## Security

### XSS Safe
```blade
$displayText = e($comment->comment);  // Escape first
// Then apply regex replacements
{!! $displayText !!}  // Safe to output
```

### SQL Injection Safe
- Uses Laravel ORM
- Parameterized queries everywhere
- No direct SQL injection possible

### User Enumeration Safe
- Only active users searchable
- Limited to 10 results
- No timing attacks

## Performance

### Frontend Optimization
- 250ms debounce on search
- AbortController cancels old requests
- 10 result limit
- Dropdown cached

### Database Optimization
- Indexed on `user_id`
- Unique constraint prevents duplicates
- Relation loading efficient with `mentions()`

### Transaction Safety
- Comment + mentions in single transaction
- If mention fails, entire comment rolled back
- No orphaned records

## Troubleshooting

### Issue: Mentions not showing in autocomplete
**Solution:** 
- Check user is active: `is_active = true`
- Check username matches exactly (case-sensitive)
- Check network tab in browser console

### Issue: Mentioned user not getting notification
**Solution:**
- Verify mention was created: check `comment_mentions` table
- Check notification was sent: check `notifications` table
- Verify mentioned user is active: `is_active = true`
- Not notified if user mentioned themselves

### Issue: Comment save fails
**Solution:**
- Check comment text is at least 3 characters
- Check attachments are valid format
- Check user has permission to comment
- Check transaction rolled back properly

## Extending the Feature

### Add @team mentions
```php
// Modify extractMentionedUsers() regex to capture @team
// Look up all team members
// Create mention record for each
```

### Add real-time notifications
```php
// Use Laravel Echo + WebSocket
// Broadcast mention event
// Frontend listens for WebSocket message
```

### Add mention history
```php
// Create `mentions` route
// Query `comment_mentions` with user filter
// Show timeline of mentions
```

## Configuration

No configuration required. Uses defaults:
- 250ms debounce (edit in `commentForm()`)
- 10 result limit (edit in `UserController@search()`)
- Mention regex pattern (edit in `extractMentionedUsers()`)

To customize, edit the respective files listed above.

## Version History

- **v1.0** (Aug 21, 2026): Initial implementation
  - @mention detection and notifications
  - Autocomplete dropdown
  - Mention highlighting
  - Self-mention prevention
  - Duplicate prevention
  - XSS protection

## Support

For issues or questions:
1. Check test cases in `MentionNotificationTest.php`
2. Review code comments in `TicketController.php`
3. Check implementation doc: `MENTION_NOTIFICATION_IMPLEMENTATION.md`