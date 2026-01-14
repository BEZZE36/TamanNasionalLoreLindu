/**
 * Show AI rate limit toast notification
 * @param {string} message - The message to display
 * @param {string|null} resetTime - When the rate limit will reset (format: HH:mm:ss)
 */
export function showAIRateLimitToast(message = 'AI Rate Limit Tercapai', resetTime = null) {
    const event = new CustomEvent('ai-rate-limit', {
        detail: { message, resetTime }
    });
    window.dispatchEvent(event);
}

/**
 * Handle AI API response and show toast if rate limited or exhausted
 * @param {Object} data - API response data
 * @returns {boolean} - Returns true if rate limited/exhausted (caller should stop processing)
 */
export function handleAIResponse(data) {
    if (!data.success) {
        // Handle rate limit
        if (data.error_type === 'rate_limit') {
            showAIRateLimitToast(
                'Batas penggunaan AI telah tercapai. Silakan coba lagi nanti.',
                data.reset_time
            );
            return true;
        }

        // Handle all keys exhausted
        if (data.error_type === 'all_exhausted') {
            showAIRateLimitToast(
                data.error || 'Semua API key dan model telah mencapai batas quota harian.',
                null
            );
            return true;
        }
    }
    return false; // Not rate limited
}
