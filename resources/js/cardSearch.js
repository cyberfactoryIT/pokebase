/**
 * Global Card Search - Typeahead functionality
 * Uses existing /api/search/cards endpoint
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('global-card-search');
    const searchDropdown = document.getElementById('search-dropdown');
    
    if (!searchInput || !searchDropdown) return;
    
    let debounceTimer = null;
    let currentRequestId = 0;
    let highlightedIndex = -1;
    let currentResults = [];
    
    // Debounced search handler
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear dropdown if query too short
        if (query.length < 2) {
            hideDropdown();
            return;
        }
        
        // Debounce: wait 300ms after last keystroke
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideDropdown();
            searchInput.blur();
            return;
        }
        
        const items = searchDropdown.querySelectorAll('a[data-result-index]');
        if (items.length === 0) return;
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = Math.min(highlightedIndex + 1, items.length - 1);
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = Math.max(highlightedIndex - 1, -1);
            updateHighlight(items);
        } else if (e.key === 'Enter' && highlightedIndex >= 0) {
            e.preventDefault();
            items[highlightedIndex].click();
        }
    });
    
    // Click outside to close
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
            hideDropdown();
        }
    });
    
    // Perform the actual search
    function performSearch(query) {
        const requestId = ++currentRequestId;
        
        // Show loading state
        showDropdown();
        searchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">Searching...</div>';
        
        // Fetch results
        fetch(`/api/search/cards?q=${encodeURIComponent(query)}&limit=12`)
            .then(response => {
                if (!response.ok) throw new Error('Search failed');
                return response.json();
            })
            .then(data => {
                // Ignore stale responses
                if (requestId !== currentRequestId) return;
                
                // API returns array directly, not wrapped in data property
                // Deduplicate by product_id
                const dedupedResults = deduplicateResults(data || []);
                displayResults(dedupedResults);
            })
            .catch(error => {
                console.error('Card search error:', error);
                if (requestId === currentRequestId) {
                    searchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">Search error. Please try again.</div>';
                }
            });
    }
    
    // Deduplicate results by product_id
    function deduplicateResults(results) {
        const seen = new Set();
        return results.filter(card => {
            // Use product_id as primary key, fallback to composite key
            const key = card.product_id || `${card.group_id}-${card.card_number}`;
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }
    
    // Display search results
    function displayResults(results) {
        highlightedIndex = -1;
        currentResults = results;
        
        if (results.length === 0) {
            searchDropdown.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found</div>';
            return;
        }
        
        const html = results.map((card, index) => {
            // Generate the card detail URL using the route pattern
            const cardUrl = `/tcg/cards/${card.product_id}`;
            
            return `
                <a href="${cardUrl}" 
                   data-result-index="${index}"
                   class="search-result-item block px-4 py-3 hover:bg-white/10 border-b border-white/10 last:border-b-0 transition"
                   style="color: var(--text)">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">${escapeHtml(card.name)}</div>
                            <div class="text-xs mt-0.5" style="color: var(--text-muted)">
                                ${escapeHtml(card.group_name || 'Unknown Set')}
                            </div>
                        </div>
                        <div class="text-xs font-mono flex-shrink-0" style="color: var(--text-muted)">
                            #${escapeHtml(card.card_number || 'N/A')}
                        </div>
                    </div>
                </a>
            `;
        }).join('');
        
        searchDropdown.innerHTML = html;
    }
    
    // Update highlighted item
    function updateHighlight(items) {
        items.forEach((item, index) => {
            if (index === highlightedIndex) {
                item.classList.add('bg-white/10');
            } else {
                item.classList.remove('bg-white/10');
            }
        });
    }
    
    // Show dropdown
    function showDropdown() {
        searchDropdown.classList.remove('hidden');
    }
    
    // Hide dropdown
    function hideDropdown() {
        searchDropdown.classList.add('hidden');
        searchDropdown.innerHTML = '';
        highlightedIndex = -1;
        currentResults = [];
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
