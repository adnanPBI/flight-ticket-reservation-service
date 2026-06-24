# Phase 10 — Reverb Support Chat

This phase adds a Reverb-ready support chat workflow.

## Included

- `ChatWidget` React component
- `StartConversationController`
- `SendMessageController`
- `ListMessagesController`
- `StartConversationAction`
- `SendChatMessageAction`
- `MergeGuestConversationAction`
- `ChatMessageCreated` broadcast event
- admin reply action on Filament support conversation view page
- `laravel-echo` and `pusher-js` frontend dependencies

## Public routes

```txt
POST /support/chat/start
GET  /support/chat/conversations/{conversation}/messages
POST /support/chat/conversations/{conversation}/messages
```

## Broadcast channel

```txt
chat.conversation.{conversationId}
```

## Local Reverb run

```bash
php artisan reverb:start
npm run dev
```

## cPanel VPS process

Run Reverb with Supervisor or systemd. Do not run it in an open terminal only.

Example:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

Then proxy WebSocket traffic through your web server / cPanel reverse proxy setup as needed.

## Security note

This starter uses a simple public broadcast channel name for easier Reverb testing. Message read/send endpoints are still protected by the visitor token cookie or authenticated user ownership checks.

For production hardening, move the broadcast channel to private channels with signed/authorized channel subscriptions.
