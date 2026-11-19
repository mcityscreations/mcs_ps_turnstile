{**
 * Turnstile Configuration Admin Template
 *}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            <i class="icon-shield"></i> {l s='Cloudflare Turnstile Configuration'}
        </h3>
    </div>
    
    <div class="panel-body">
        <div class="alert alert-info">
            <strong>{l s='Getting Started'}</strong>
            <p>{l s='To use this module, you need to get your Site Key and Secret Key from Cloudflare Dashboard:'}</p>
            <ol>
                <li>{l s='Visit %url%' sprintf=['url' => 'https://dash.cloudflare.com/']}</li>
                <li>{l s='Navigate to AI & Security > Turnstile'}</li>
                <li>{l s='Create a new Turnstile site or use an existing one'}</li>
                <li>{l s='Copy the Site Key and Secret Key below'}</li>
            </ol>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{l s='Enable Module'}</label>
            <div class="col-sm-9">
                <span id="module_status" class="badge" style="font-size: 14px;">
                    {if $module_enabled}
                        <span class="badge-success">{l s='Enabled'}</span>
                    {else}
                        <span class="badge-danger">{l s='Disabled'}</span>
                    {/if}
                </span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{l s='Protected Forms'}</label>
            <div class="col-sm-9">
                <ul>
                    {foreach from=$protected_forms item=form key=key}
                        <li><i class="icon-check text-success"></i> {$form|escape:'html'}</li>
                    {/foreach}
                </ul>
                {if !$protected_forms}
                    <p class="text-muted">{l s='No forms currently protected'}</p>
                {/if}
            </div>
        </div>

        <hr>

        <div class="alert alert-warning">
            <strong>{l s='Important Notes'}</strong>
            <ul>
                <li>{l s='This module integrates with Cloudflare Turnstile v0 API'}</li>
                <li>{l s='Turnstile widget is non-intrusive and user-friendly'}</li>
                <li>{l s='Module automatically handles server-side validation'}</li>
                <li>{l s='No impact on SEO or core Prestashop functionality'}</li>
            </ul>
        </div>
    </div>
</div>
