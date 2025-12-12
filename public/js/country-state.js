// Country/State dropdown helper (no API calls; uses inline data if provided)
(function(){
    // Country/State auto-select disabled: keep fields as plain text inputs.
    function applyCountryState() {
        // no-op
    }
    // Expose in case existing code calls it; it simply does nothing now.
    window.applyCountryState = applyCountryState;
})();