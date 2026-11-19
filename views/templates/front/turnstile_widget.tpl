{**
 * Turnstile Widget Template
 * Renders the Cloudflare Turnstile CAPTCHA widget
 *}

<div class="mcs-turnstile-widget" style="margin: 15px 0;">
    <div class="cf-turnstile" 
         data-sitekey="{$site_key|escape:'html'}" 
         data-theme="light"
         data-size="normal">
    </div>
</div>

<script>
    if (!window.turnstileLoaded) {
        window.turnstileLoaded = true;
        var script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }
</script>

<style>
    .mcs-turnstile-widget {
        display: flex;
        justify-content: center;
    }
    
    .cf-turnstile {
        display: inline-block;
    }
</style>
