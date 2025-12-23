<div>
    @if(auth()->user()->isStudent())
        <!-- Floating Chat Button -->
        <button
            wire:click="toggleChat"
            class="fixed bottom-6 right-6 z-50 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-full p-4 shadow-2xl transition-all duration-300 hover:scale-110 active:scale-95 ring-4 ring-purple-200 dark:ring-purple-900/30"
            title="Placement Assistant - Li-A">
            @if($isOpen)
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            @else
                <!-- Chat icon SVG with animation -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6 animate-pulse">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            @endif
        </button>

        <!-- Chat Window -->
        @if($isOpen)
            <div class="fixed bottom-24 right-6 z-50 w-96 max-w-[calc(100vw-3rem)] h-[600px] max-h-[calc(100vh-8rem)] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl flex flex-col border border-gray-200 dark:border-gray-700 overflow-hidden backdrop-blur-sm animate-in slide-in-from-bottom-5 duration-300">
                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 dark:from-purple-700 dark:to-purple-800 text-white p-4 flex justify-between items-center shadow-lg">
                    <div class="flex items-center gap-3">
                        <!-- Avatar -->
                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center ring-2 ring-white/30">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Li-A</h3>
                            <p class="text-xs text-purple-100 flex items-center gap-1">
                                <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                                Online
                            </p>
                        </div>
                    </div>
                    <button wire:click="toggleChat" class="text-white hover:text-gray-200 hover:bg-white/10 rounded-full p-1.5 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Messages Container -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800 chat-scrollbar" id="chat-messages">
                    @foreach($messages as $index => $message)
                        <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }} animate-in fade-in slide-in-from-bottom-2 duration-300">
                            @if($message['type'] === 'user')
                                <!-- User Message -->
                                <div class="max-w-[80%] rounded-xl rounded-tr-sm p-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white shadow-sm">
                                    <div class="text-sm whitespace-pre-wrap leading-snug">{{ $message['content'] }}</div>
                                    <div class="text-xs mt-1 opacity-80 text-right">
                                        {{ $message['timestamp']->format('H:i') }}
                                    </div>
                                </div>
                            @else
                                <!-- Bot Message -->
                                <div class="max-w-[85%] flex gap-2">
                                    <!-- Bot Avatar -->
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center flex-shrink-0 mt-0.5 ring-2 ring-purple-200 dark:ring-purple-900">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-white">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 rounded-xl rounded-tl-sm p-2.5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm border border-gray-200 dark:border-gray-600">
                                        <div class="text-sm whitespace-pre-line leading-snug text-gray-800 dark:text-gray-200">
                                            {!! nl2br(e($message['content'])) !!}
                                        </div>
                                        <div class="text-xs mt-1.5 opacity-70 text-gray-500 dark:text-gray-400">
                                            {{ $message['timestamp']->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Show buttons for bot messages -->
                        @if($message['type'] === 'bot' && isset($messageButtons[$index]) && count($messageButtons[$index]) > 0)
                            <div class="flex justify-start ml-9 mb-1">
                                <div class="max-w-[85%] w-full">
                                    <div class="grid grid-cols-1 gap-1.5">
                                        @foreach($messageButtons[$index] as $button)
                                            @php
                                                $action = $button['action'];
                                                $data = isset($button['data']) ? $button['data'] : null;
                                                $dataParam = $data !== null ? $data : 'null';
                                            @endphp
                                            <button
                                                wire:click="buttonAction('{{ $action }}', {{ $dataParam }})"
                                                wire:loading.attr="disabled"
                                                class="w-full px-3 py-2 text-xs font-medium bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/40 dark:to-purple-800/40 text-purple-700 dark:text-purple-200 rounded-lg hover:from-purple-100 hover:to-purple-200 dark:hover:from-purple-900/60 dark:hover:to-purple-800/60 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed border border-purple-200 dark:border-purple-700 shadow-sm hover:shadow-md hover:scale-[1.02] active:scale-[0.98] text-left">
                                                <span wire:loading.remove wire:target="buttonAction('{{ $action }}', {{ $dataParam }})" class="flex items-center gap-2">
                                                    {{ $button['label'] }}
                                                </span>
                                                <span wire:loading wire:target="buttonAction('{{ $action }}', {{ $dataParam }})" class="flex items-center justify-center gap-2">
                                                    <x-loading-spinner size="h-3 w-3" color="text-purple-600 dark:text-purple-300" />
                                                    Processing...
                                                </span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($isTyping)
                        <div class="flex justify-start animate-in fade-in slide-in-from-bottom-2 duration-300">
                            <div class="flex gap-2 items-end">
                                <!-- Bot Avatar -->
                                <div class="w-7 h-7 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center flex-shrink-0 ring-2 ring-purple-200 dark:ring-purple-900">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-white">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                                    </svg>
                                </div>
                                <div class="bg-white dark:bg-gray-700 rounded-xl rounded-tl-sm p-2.5 shadow-sm border border-gray-200 dark:border-gray-600">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-purple-400 dark:bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                                        <div class="w-2 h-2 bg-purple-400 dark:bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                        <div class="w-2 h-2 bg-purple-400 dark:bg-purple-500 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input Area (for games and tips) -->
                @if($currentGame || $showingTipForm)
                    <div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-t from-gray-50 to-white dark:from-gray-900 dark:to-gray-800/50 backdrop-blur-sm">
                        @if($currentGame)
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    wire:model="gameAnswer"
                                    wire:keydown.enter="buttonAction('submit_game_answer', null)"
                                    placeholder="@if($currentGame === 'word') Enter the unscrambled word @else Enter your answer (A, B, C, or D) @endif"
                                    class="flex-1 px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 shadow-sm transition-all"
                                    autocomplete="off">
                                <button
                                    wire:click="buttonAction('submit_game_answer', null)"
                                    wire:loading.attr="disabled"
                                    wire:target="submit_game_answer"
                                    class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span wire:loading.remove wire:target="submit_game_answer">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="submit_game_answer">
                                        <x-loading-spinner size="h-5 w-5" color="text-white" />
                                    </span>
                                </button>
                            </div>
                        @elseif($showingTipForm)
                            <div class="space-y-3">
                                <input
                                    type="text"
                                    wire:model="tipNickname"
                                    placeholder="Enter your nickname (can be anonymous)"
                                    class="w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 shadow-sm transition-all"
                                    autocomplete="off">
                                <textarea
                                    wire:model="tipContent"
                                    placeholder="Share your tip or advice here..."
                                    rows="3"
                                    class="w-full px-4 py-2.5 text-sm border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 resize-none shadow-sm transition-all"
                                    autocomplete="off"></textarea>
                                <button
                                    wire:click="buttonAction('submit_tip', null)"
                                    wire:loading.attr="disabled"
                                    wire:target="submit_tip"
                                    class="w-full px-4 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                                    <span wire:loading.remove wire:target="submit_tip" class="flex items-center justify-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Submit Tip
                                    </span>
                                    <span wire:loading wire:target="submit_tip" class="flex items-center justify-center gap-2">
                                        <x-loading-spinner size="h-4 w-4" color="text-white" />
                                        Submitting...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Info Message (Input disabled) -->
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-t from-purple-50/50 to-transparent dark:from-purple-900/10 dark:to-transparent">
                        <p class="text-xs text-gray-600 dark:text-gray-400 text-center flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-purple-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                            Use the buttons above to interact with me
                        </p>
                    </div>
                @endif
            </div>
        @endif
    @endif

    @once
        @push('styles')
        <style>
            /* Custom Scrollbar for Chat */
            .chat-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .chat-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .chat-scrollbar::-webkit-scrollbar-thumb {
                background: rgba(147, 51, 234, 0.3);
                border-radius: 10px;
            }

            .chat-scrollbar::-webkit-scrollbar-thumb:hover {
                background: rgba(147, 51, 234, 0.5);
            }

            /* Smooth animations */
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .animate-in {
                animation: slideIn 0.3s ease-out;
            }

            /* Better message formatting */
            #chat-messages .whitespace-pre-line {
                line-height: 1.7;
                word-wrap: break-word;
            }

            /* Mobile responsive adjustments */
            @media (max-width: 640px) {
                .fixed.bottom-24.right-6 {
                    bottom: 20px;
                    right: 20px;
                    left: 20px;
                    width: auto;
                    max-width: none;
                    height: calc(100vh - 100px);
                    max-height: calc(100vh - 100px);
                }
            }
        </style>
        @endpush
    @endonce
</div>

@once
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            // Auto-scroll to bottom when new message arrives
            Livewire.on('scroll-to-bottom', () => {
                setTimeout(() => {
                    const messagesContainer = document.getElementById('chat-messages');
                    if (messagesContainer) {
                        messagesContainer.scrollTo({
                            top: messagesContainer.scrollHeight,
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            });

            // Scroll to bottom after Livewire updates
            Livewire.hook('morph.updated', () => {
                setTimeout(() => {
                    const messagesContainer = document.getElementById('chat-messages');
                    if (messagesContainer) {
                        messagesContainer.scrollTo({
                            top: messagesContainer.scrollHeight,
                            behavior: 'smooth'
                        });
                    }
                }, 100);
            });
        });
    </script>
    @endpush
@endonce

