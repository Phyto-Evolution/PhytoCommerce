{foreach $categories as $cat}
<div style="padding:2px 0;">
    <i class="icon-folder{if !empty($cat.children)}-open{/if}" style="color:#aaa;"></i>
    <span style="font-size:13px;">{$cat.name}</span>
</div>
{if !empty($cat.children)}
    <div style="margin-left:15px;">
        {include file='module:phytoquickadd/views/templates/admin/category_tree.tpl'
                 categories=$cat.children}
    </div>
{/if}
{/foreach}
