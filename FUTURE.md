# Future Work

All features from the PRD and NEXT.md expansion plan have been implemented. Two minor gaps remain for polish.

## 1. Mobile-Responsive Table Views

### Gap
5 index pages use `<table>` wrapped in `overflow-x-auto` instead of converting to card layouts on mobile screens.

### Files to update
- `resources/js/Pages/Portal/Projects/Index.vue`
- `resources/js/Pages/Portal/Meetings/Index.vue`
- `resources/js/Pages/Portal/Tickets/Index.vue`
- `resources/js/Pages/Projects/Index.vue`
- `resources/js/Pages/Tickets/Index.vue`

### Implementation notes
- Hide `<table>` on mobile (`hidden md:table`)
- Add a card-based layout visible only on mobile (`block md:hidden`)
- Each row becomes a bordered card with label-value pairs
- Include action buttons (edit, delete, etc.) per card
- Keep checkbox selection working in card mode
- Ensure touch targets are at least `h-10 w-10` (44px)

---

## 2. @mention Autocomplete UI

### Gap
`CommentSection.vue` at `resources/js/Components/CommentSection.vue` parses `@Name` mentions server-side via regex in `StoreCommentAction`, but has no frontend typeahead/autocomplete dropdown.

### Implementation notes
- Add a team members endpoint (or pass `:members` prop) to fetch available users
- Detect `@` character in the textarea and show a positioned dropdown with matching member names
- Filter members as the user types after `@`
- On selection, insert the full `@Name` into the textarea
- Keep keyboard navigation (arrow keys + enter/tab)
- The server-side `StoreCommentAction` already handles parsing and notification
