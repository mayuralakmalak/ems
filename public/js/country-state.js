// Country/State dropdown helper
(function(){
    function applyCountryState() {
        const countrySelect = document.getElementById('country');
        const stateSelect = document.getElementById('state');
        
        if (!countrySelect || !stateSelect) {
            return;
        }
        
        // Load states when country changes
        countrySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const countryId = selectedOption
                ? (selectedOption.getAttribute('data-id') || selectedOption.value)
                : this.value;

            stateSelect.innerHTML = '<option value="">Select State</option>';
            
            if (!countryId) {
                return;
            }
            
            // Decide whether we want state IDs or names as values
            const valueField = stateSelect.getAttribute('data-value-field') || 'id';

            // Fetch states for selected country (URL from Laravel route helper if provided)
            const apiUrl = (window.statesApiUrl || '/api/states') + '?country_id=' + countryId;
            
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.states && data.states.length > 0) {
                    data.states.forEach(state => {
                        const option = document.createElement('option');
                        option.value = valueField === 'name' ? state.name : state.id;
                        option.textContent = state.name;
                        stateSelect.appendChild(option);
                    });
                    
                    // Set old value if exists
                    const oldState = stateSelect.getAttribute('data-old-value');
                    if (oldState) {
                        stateSelect.value = oldState;
                    }
                }
            })
            .catch(error => {
                console.error('Error loading states:', error);
            });
        });
        
        // If country is pre-selected (from old input), load states
        const oldCountry = countrySelect.value;
        if (oldCountry) {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                countrySelect.dispatchEvent(new Event('change'));
            }, 100);
        }
    }
    
    window.applyCountryState = applyCountryState;
})();