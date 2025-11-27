<div>
    @if(auth()->user()->isStudent())
        <!-- Floating Chat Button -->
        <button
            wire:click="toggleChat"
            class="fixed bottom-6 right-6 z-50 bg-purple-600 hover:bg-purple-700 text-white rounded-full p-4 shadow-lg transition-all duration-300 hover:scale-110"
            title="Placement Assistant">
            @if($isOpen)
                <x-heroicon name="x-mark" class="h-6 w-6" />
            @else
                <!-- Chat icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                </svg>
            @endif
        </button>

        <!-- Chat Window -->
        @if($isOpen)
            <div class="fixed bottom-24 right-6 z-50 w-96 h-[600px] bg-white dark:bg-gray-800 rounded-lg shadow-2xl flex flex-col border border-gray-200 dark:border-gray-700">
                <!-- Header -->
                <div class="bg-purple-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                    <div>
                        <h3 class="font-semibold text-lg">Li-A</h3>
                        <p class="text-sm text-purple-100">I'm here to help!</p>
                    </div>
                    <button wire:click="toggleChat" class="text-white hover:text-gray-200">
                        <x-heroicon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                <!-- Messages Container -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
                    @foreach($messages as $index => $message)
                        <div class="flex {{ $message['type'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg p-3 {{ $message['type'] === 'user' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }}">
                                <div class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</div>
                                <div class="text-xs mt-1 opacity-70">
                                    {{ $message['timestamp']->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($isTyping)
                        <div class="flex justify-start">
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                @if(count($quickActions) > 0)
                    <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Quick Actions:</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($quickActions as $action)
                                <button
                                    wire:click="quickAction('{{ $action }}')"
                                    class="px-3 py-1 text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full hover:bg-purple-200 dark:hover:bg-purple-900/50 transition-colors">
                                    {{ $action }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Input Area -->
                <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                    <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                        <input
                            type="text"
                            wire:model="currentMessage"
                            placeholder="Type your message..."
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-gray-200"
                            autocomplete="off">
                        <button
                            type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            <!-- Send icon -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Auto-scroll to bottom when new message arrives
        Livewire.on('scroll-to-bottom', () => {
            const messagesContainer = document.getElementById('chat-messages');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });

        // Scroll to bottom after Livewire updates
        Livewire.hook('morph.updated', () => {
            setTimeout(() => {
                const messagesContainer = document.getElementById('chat-messages');
                if (messagesContainer) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
            }, 100);
        });
    });
</script>

