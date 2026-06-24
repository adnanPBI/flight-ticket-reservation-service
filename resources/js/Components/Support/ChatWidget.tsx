import { useEffect, useMemo, useRef, useState } from 'react';
import type { FormEvent } from 'react';

type ChatMessage = {
  id: number;
  sender_type: 'customer' | 'admin' | 'system' | string;
  sender_id?: number | null;
  body: string;
  created_at?: string | null;
};

type StartResponse = {
  conversation: {
    id: number;
    status: string;
    booking_reference?: string | null;
    messages: ChatMessage[];
  };
  visitor_token: string;
  broadcast_channel: string;
};

function csrf() {
  return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}

export default function ChatWidget({ bookingReference }: { bookingReference?: string | null }) {
  const [open, setOpen] = useState(false);
  const [conversationId, setConversationId] = useState<number | null>(null);
  const [channelName, setChannelName] = useState<string | null>(null);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [body, setBody] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const listRef = useRef<HTMLDivElement | null>(null);

  const startPayload = useMemo(() => ({ booking_reference: bookingReference ?? undefined }), [bookingReference]);

  useEffect(() => {
    if (!open || conversationId) {
      return;
    }

    setLoading(true);
    fetch('/support/chat/start', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        Accept: 'application/json',
      },
      body: JSON.stringify(startPayload),
    })
      .then(async (response) => {
        if (!response.ok) throw new Error('Could not start support chat.');
        return (await response.json()) as StartResponse;
      })
      .then((data) => {
        setConversationId(data.conversation.id);
        setChannelName(data.broadcast_channel);
        setMessages(data.conversation.messages ?? []);
      })
      .catch((caught) => setError(caught instanceof Error ? caught.message : 'Chat failed to start.'))
      .finally(() => setLoading(false));
  }, [open, conversationId, startPayload]);

  useEffect(() => {
    if (!channelName || !window.Echo) {
      return;
    }

    const channel = window.Echo.private(channelName);
    channel.listen('.chat.message.created', (event: { message: ChatMessage }) => {
      setMessages((current) => current.some((message) => message.id === event.message.id) ? current : [...current, event.message]);
    });

    return () => {
      window.Echo?.leave(channelName);
    };
  }, [channelName]);

  useEffect(() => {
    listRef.current?.scrollTo({ top: listRef.current.scrollHeight, behavior: 'smooth' });
  }, [messages.length, open]);

  async function sendMessage(event: FormEvent) {
    event.preventDefault();
    if (!conversationId || body.trim() === '') return;

    const pendingBody = body.trim();
    setBody('');
    setError(null);

    const response = await fetch(`/support/chat/conversations/${conversationId}/messages`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf(),
        Accept: 'application/json',
      },
      body: JSON.stringify({ body: pendingBody }),
    });

    if (!response.ok) {
      setError('Message could not be sent.');
      setBody(pendingBody);
      return;
    }

    const data = await response.json() as { message: ChatMessage };
    setMessages((current) => current.some((message) => message.id === data.message.id) ? current : [...current, data.message]);
  }

  return (
    <div id="support" className="fixed bottom-4 right-4 z-40 w-[calc(100vw-2rem)] max-w-sm md:right-6">
      {open && (
        <div className="mb-3 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
          <div className="bg-slate-950 p-4 text-white">
            <div className="flex items-center justify-between gap-4">
              <div>
                <p className="text-xs font-bold uppercase tracking-[0.25em] text-slate-300">Support</p>
                <h3 className="text-lg font-black">Need help?</h3>
              </div>
              <span className="rounded-full bg-white/10 px-3 py-1 text-xs font-black">Phase 10</span>
            </div>
          </div>

          <div ref={listRef} className="h-72 space-y-3 overflow-y-auto bg-slate-50 p-4">
            {loading && <p className="text-sm font-semibold text-slate-500">Starting chat…</p>}
            {error && <p className="rounded-2xl bg-rose-50 p-3 text-sm font-bold text-rose-600">{error}</p>}
            {messages.length === 0 && !loading && (
              <div className="rounded-2xl bg-white p-4 text-sm text-slate-600 shadow-sm">
                Ask about a booking, payment, fare, or issue. Messages are saved to the admin support inbox.
              </div>
            )}
            {messages.map((message) => {
              const isCustomer = message.sender_type === 'customer';
              return (
                <div key={message.id} className={`flex ${isCustomer ? 'justify-end' : 'justify-start'}`}>
                  <div className={`max-w-[85%] rounded-2xl px-4 py-3 text-sm shadow-sm ${isCustomer ? 'bg-slate-950 text-white' : 'bg-white text-slate-700'}`}>
                    <p>{message.body}</p>
                    <p className={`mt-1 text-[10px] font-semibold ${isCustomer ? 'text-slate-300' : 'text-slate-400'}`}>
                      {message.created_at ? new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'now'}
                    </p>
                  </div>
                </div>
              );
            })}
          </div>

          <form onSubmit={sendMessage} className="flex gap-2 border-t border-slate-200 bg-white p-3">
            <input
              value={body}
              onChange={(event) => setBody(event.target.value)}
              placeholder="Type your message…"
              className="min-w-0 flex-1 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-slate-950"
            />
            <button className="rounded-2xl bg-slate-950 px-4 py-3 text-sm font-black text-white" type="submit">
              Send
            </button>
          </form>
        </div>
      )}

      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="ml-auto flex rounded-full bg-slate-950 px-5 py-3 text-sm font-black text-white shadow-2xl"
      >
        {open ? 'Close chat' : 'Support chat'}
      </button>
    </div>
  );
}
