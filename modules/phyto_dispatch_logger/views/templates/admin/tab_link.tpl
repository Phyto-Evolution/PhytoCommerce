{**
 * Phyto Dispatch Logger — admin order detail tab link
 *
 * Injected via hookDisplayAdminOrderTabLink.
 * Rendered as a <li> inside the order detail tab strip.
 *}

<li id="phyto_dispatch_logger_tab" role="presentation">
    <a href="#phyto_dispatch_logger_content"
       data-toggle="tab"
       role="tab"
       aria-controls="phyto_dispatch_logger_content">
        <i class="icon icon-truck"></i>
        {l s='Dispatch Log' mod='phyto_dispatch_logger'}
        {if $pdl_has_log}
            <span class="badge badge-success" title="{l s='Log entry exists' mod='phyto_dispatch_logger'}">&#10003;</span>
        {/if}
    </a>
</li>
