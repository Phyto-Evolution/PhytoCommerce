<style id="phyto-kyc-blur-css">
{$phyto_kyc_blur_css nofilter}
</style>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('kyc-blur-active');
    });
    // Also add immediately in case DOMContentLoaded already fired
    if (document.readyState !== 'loading') {
        document.body && document.body.classList.add('kyc-blur-active');
    }
})();
</script>
