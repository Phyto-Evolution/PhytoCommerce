{**
 * Admin product tab — recent Grower's Journal entries for the current product.
 *
 * Variables assigned by hookDisplayAdminProductsExtra:
 *   $phyto_entries     array   All entries (approved and unapproved) for this product
 *   $phyto_admin_link  string  URL to the full AdminPhytoGrowersJournal list filtered by product
 *   $phyto_add_link    string  URL to create a new entry pre-filled with this product
 *   $id_product        int     Current product ID
 *}

<div class="panel phyto-journal-admin-tab">
  <div class="panel-heading">
    <i class="icon-leaf"></i>
    {l s="Grower's Journal" mod='phyto_growers_journal'}
    <span class="panel-heading-action">
      <a href="{$phyto_add_link|escape:'html':'UTF-8'}" class="btn btn-default btn-xs">
        <i class="icon-plus"></i>
        {l s='Add Entry' mod='phyto_growers_journal'}
      </a>
      <a href="{$phyto_admin_link|escape:'html':'UTF-8'}" class="btn btn-default btn-xs">
        <i class="icon-list"></i>
        {l s='View All' mod='phyto_growers_journal'}
      </a>
    </span>
  </div>

  <div class="panel-body">
    {if $phyto_entries|@count > 0}
      <table class="table tableDnD phyto-journal-admin-table">
        <thead>
          <tr>
            <th><span class="title_box">{l s='Date' mod='phyto_growers_journal'}</span></th>
            <th><span class="title_box">{l s='Title' mod='phyto_growers_journal'}</span></th>
            <th><span class="title_box">{l s='Type' mod='phyto_growers_journal'}</span></th>
            <th class="text-center"><span class="title_box">{l s='Approved' mod='phyto_growers_journal'}</span></th>
            <th class="text-center"><span class="title_box">{l s='Photos' mod='phyto_growers_journal'}</span></th>
          </tr>
        </thead>
        <tbody>
          {foreach $phyto_entries as $entry}
            <tr>
              <td>{$entry.entry_date|escape:'html':'UTF-8'}</td>
              <td>
                <strong>{$entry.title|escape:'html':'UTF-8'}</strong>
                {if $entry.id_customer > 0}
                  <br>
                  <small class="text-muted">
                    <i class="icon-user"></i>
                    {$entry.firstname|escape:'html':'UTF-8'} {$entry.lastname|escape:'html':'UTF-8'}
                  </small>
                {/if}
              </td>
              <td>
                {if $entry.entry_type == 'Milestone'}
                  <span class="label label-warning">{l s='Milestone' mod='phyto_growers_journal'}</span>
                {elseif $entry.entry_type == 'Customer'}
                  <span class="label label-info">{l s='Customer' mod='phyto_growers_journal'}</span>
                {else}
                  <span class="label label-success">{l s='Store' mod='phyto_growers_journal'}</span>
                {/if}
              </td>
              <td class="text-center">
                {if $entry.approved}
                  <span class="label label-success"><i class="icon-check"></i></span>
                {else}
                  <span class="label label-danger"><i class="icon-times"></i></span>
                {/if}
              </td>
              <td class="text-center">
                {assign var='photo_count' value=0}
                {if $entry.photo1}{assign var='photo_count' value=$photo_count+1}{/if}
                {if $entry.photo2}{assign var='photo_count' value=$photo_count+1}{/if}
                {if $entry.photo3}{assign var='photo_count' value=$photo_count+1}{/if}
                {if $photo_count > 0}
                  <span class="label label-default"><i class="icon-camera"></i> {$photo_count}</span>
                {else}
                  <span class="text-muted">—</span>
                {/if}
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>

      <div class="phyto-journal-admin-footer">
        <a href="{$phyto_admin_link|escape:'html':'UTF-8'}" class="btn btn-link btn-sm">
          {l s='Manage all entries in the full admin page' mod='phyto_growers_journal'}
          <i class="icon-external-link"></i>
        </a>
      </div>

    {else}
      <div class="alert alert-info phyto-journal-empty">
        <i class="icon-info-circle"></i>
        {l s='No journal entries yet for this product.' mod='phyto_growers_journal'}
        <a href="{$phyto_add_link|escape:'html':'UTF-8'}" class="alert-link">
          {l s='Create the first entry.' mod='phyto_growers_journal'}
        </a>
      </div>
    {/if}
  </div>
</div>
