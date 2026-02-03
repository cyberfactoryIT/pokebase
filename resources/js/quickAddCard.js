/**
 * Quick Card Search - Dashboard typeahead that navigates to card detail
 * Supports all backends: TCGCSV, TCGDEX, CMAPI
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('card-search');
    const searchResults = document.getElementById('search-results');
    const wrapper = searchInput?.closest('[data-catalog-backend]');
    
    if (!searchInput || !searchResults || !wrapper) {
        console.log('Quick Search: Elements not found on this page');
        return;
    }
    
    const catalogBackend = wrapper.dataset.catalogBackend || 'tcgcsv';
    const gameSlug = wrapper.dataset.gameSlug || 'pokemon';
    
    let debounceTimer = null;
    let currentRequestId = 0;
    let highlightedIndex = -1;
    
    // Debounced search handler
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear dropdown if query too short
        if (query.length < 2) {
            hideResults();
            highlightedIndex = -1;
            return;
        }
        
        // Debounce: wait 300ms after last keystroke
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(query);
        }, 300);
    });
    
    // Keyboard navigation: ArrowUp, ArrowDown, Enter, Escape
    searchInput.addEventListener('keydown', function(e) {
        const items = searchResults.querySelectorAll('a[data-result-index]');
        
        if (e.key === 'Escape') {
            hideResults();
            searchInput.blur();
            return;
        }
        
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
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            hideResults();
        }
    });
    
    // Perform the actual search
    function performSearch(query) {
        const requestId = ++currentRequestId;
        
        // Show loading state
        showResults();
        searchResults.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">Searching...</div>';
        
        // Fetch results with backend parameter
        fetch(`/api/search/cards?q=${encodeURIComponent(query)}&limit=12&backend=${catalogBackend}`)
            .then(response => {
                if (!response.ok) throw new Error('Search failed');
                return response.json();
            })
            .then(data => {
                // Ignore stale responses
                if (requestId !== currentRequestId) return;
                
                // Deduplicate results
                const dedupedResults = deduplicateResults(data || []);
                displayResults(dedupedResults);
            })
            .catch(error => {
                console.error('Quick Search error:', error);
                if (requestId === currentRequestId) {
                    searchResults.innerHTML = '<div class="px-4 py-3 text-red-400 text-sm">Search error. Please try again.</div>';
                }
            });
    }
    
    // Deduplicate results by unique card identifier
    function deduplicateResults(results) {
        const seen = new Set();
        return results.filter(card => {
            let key;
            if (card.backend === 'tcgdex') {
                key = card.tcgdex_id;
            } else if (card.backend === 'cmapi') {
                key = card.cmapi_id;
            } else {
                key = card.product_id || `${card.group_id}-${card.card_number}`;
            }
            
            if (seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }
    
    // Update highlighted item
    function updateHighlight(items) {
        items.forEach((item, index) => {
            if (index === highlightedIndex) {
                item.classList.add('bg-white/20');
                item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            } else {
                item.classList.remove('bg-white/20');
            }
        });
    }
    
    // Display search results
    function displayResults(results) {
        highlightedIndex = -1;
        
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found</div>';
            return;
        }
        
        const html = results.map((card, index) => {
            // Generate the card detail URL based on backend
            let cardUrl;
            if (card.backend === 'tcgdex') {
                cardUrl = `/pokemon/cards/${card.tcgdex_id}`;
            } else if (card.backend === 'cmapi') {
                cardUrl = `/${gameSlug}/cards/${card.cmapi_id}`;
            } else {
                // TCGCSV
                cardUrl = `/tcg/cards/${card.product_id}`;
            }
            
            return `
                <a href="${cardUrl}" 
                   data-result-index="${index}"
                   class="search-result-item block px-4 py-3 hover:bg-white/10 border-b border-white/10 last:border-b-0 transition text-white">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm truncate">${escapeHtml(card.name)}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                ${escapeHtml(card.set_name || card.group_name || 'Unknown Set')}
                            </div>
                        </div>
                        <div class="text-xs font-mono text-gray-400 flex-shrink-0">
                            #${escapeHtml(card.card_number || 'N/A')}${card.set_total ? '/' + card.set_total : ''}
                        </div>
                    </div>
                </a>
            `;
        }).join('');
        
        searchResults.innerHTML = html;
    }
    
    // Show results dropdown
    function showResults() {
        searchResults.classList.remove('hidden');
    }
    
    // Hide results dropdown
    function hideResults() {
        searchResults.classList.add('hidden');
        searchResults.innerHTML = '';
        highlightedIndex = -1;
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
