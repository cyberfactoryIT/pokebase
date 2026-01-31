/**
 * Quick Add Card - Dashboard form with typeahead functionality
 * Uses same /api/search/cards endpoint as global search
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('card-search');
    const searchResults = document.getElementById('search-results');
    const selectedCardId = document.getElementById('selected-card-id');
    const form = document.getElementById('quick-add-form');
    const messageDiv = document.getElementById('quick-add-message');
    
    if (!searchInput || !searchResults || !form) {
        console.log('Quick Add: Elements not found on this page');
        return;
    }
    
    let debounceTimer = null;
    let currentRequestId = 0;
    
    // Debounced search handler
    searchInput.addEventListener('input', function(e) {
        const query = e.target.value.trim();
        
        // Clear dropdown if query too short
        if (query.length < 2) {
            hideResults();
            return;
        }
        
        // Debounce: wait 300ms after last keystroke
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(query);
        }, 300);
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
        
        // Fetch results
        fetch(`/api/search/cards?q=${encodeURIComponent(query)}&limit=12`)
            .then(response => {
                if (!response.ok) throw new Error('Search failed');
                return response.json();
            })
            .then(data => {
                // Ignore stale responses
                if (requestId !== currentRequestId) return;
                
                displayResults(data || []);
            })
            .catch(error => {
                console.error('Quick Add search error:', error);
                if (requestId === currentRequestId) {
                    searchResults.innerHTML = '<div class="px-4 py-3 text-red-400 text-sm">Search error. Please try again.</div>';
                }
            });
    }
    
    // Display search results
    function displayResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="px-4 py-3 text-gray-400 text-sm">No cards found</div>';
            return;
        }
        
        const html = results.map(card => {
            const imageHtml = card.image_url 
                ? `<img src="${card.image_url}" class="w-12 h-16 object-cover rounded" alt="${escapeHtml(card.name)}">`
                : `<div class="w-12 h-16 bg-white/5 rounded flex items-center justify-center">
                     <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                     </svg>
                   </div>`;
            
            return `
                <div class="search-result-card flex items-center gap-3 p-3 hover:bg-white/5 cursor-pointer transition border-b border-white/5 last:border-0" 
                     data-card-id="${card.product_id || card.tcgdex_card_id}" 
                     data-tcgdex-id="${card.tcgdex_card_id || ''}"
                     data-card-name="${escapeHtml(card.name)}">
                    ${imageHtml}
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-white truncate">${escapeHtml(card.name)}</div>
                        <div class="text-sm text-gray-400 truncate">${escapeHtml(card.set_name || card.group_name || 'Unknown Set')}</div>
                        ${card.card_number ? `<div class="text-xs text-gray-500">#${escapeHtml(card.card_number)}${card.set_total ? '/' + card.set_total : ''}</div>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        searchResults.innerHTML = html;
        
        // Add click handlers to all result cards
        searchResults.querySelectorAll('.search-result-card').forEach(card => {
            card.addEventListener('click', function() {
                selectCard(this);
            });
        });
    }
    
    // Select a card from results
    function selectCard(element) {
        const cardId = element.dataset.cardId;
        const tcgdexId = element.dataset.tcgdexId;
        const cardName = element.dataset.cardName;
        
        selectedCardId.value = cardId;
        searchInput.value = cardName;
        
        // Store card type in hidden field or data attribute for backend
        if (tcgdexId) {
            selectedCardId.setAttribute('data-is-tcgdex', 'true');
            selectedCardId.setAttribute('data-tcgdex-id', tcgdexId);
        } else {
            selectedCardId.removeAttribute('data-is-tcgdex');
            selectedCardId.removeAttribute('data-tcgdex-id');
        }
        
        hideResults();
        
        console.log('Card selected:', { cardId, tcgdexId, cardName });
    }
    
    // Show results dropdown
    function showResults() {
        searchResults.classList.remove('hidden');
    }
    
    // Hide results dropdown
    function hideResults() {
        searchResults.classList.add('hidden');
    }
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!selectedCardId.value) {
            showMessage('error', 'Please select a card from the search results first');
            return;
        }
        
        const formData = new FormData(form);
        
        // Add TCGDEX card ID if applicable
        if (selectedCardId.hasAttribute('data-is-tcgdex')) {
            formData.delete('card_id'); // Remove product_id
            formData.append('tcgdex_card_id', selectedCardId.getAttribute('data-tcgdex-id'));
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Save original button content
        const originalBtnContent = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        
        fetch(form.action || '/collection/quick-add', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showMessage('success', data.message || 'Card added successfully!');
                form.reset();
                selectedCardId.value = '';
                
                // Reload page after 1 second to show updated stats
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showMessage('error', data.message || 'Error adding card');
            }
        })
        .catch(err => {
            console.error('Add card error:', err);
            showMessage('error', 'Error adding card. Please try again.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        });
    });
    
    // Show message
    function showMessage(type, text) {
        const bgColor = type === 'success' 
            ? 'bg-green-900/30 border-green-500/30 text-green-300' 
            : 'bg-red-900/30 border-red-500/30 text-red-300';
        
        messageDiv.className = `mt-4 p-4 rounded-lg border ${bgColor}`;
        messageDiv.textContent = text;
        messageDiv.classList.remove('hidden');
        
        setTimeout(() => {
            messageDiv.classList.add('hidden');
        }, 5000);
    }
});
