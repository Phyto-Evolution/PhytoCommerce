{extends file='page.tpl'}

{block name='page_content_container'}
<main id="contact" style="padding:48px 0 80px;background:var(--fsl-off-white);">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div class="row g-5 justify-content-center">
      <div class="col-lg-6">
        <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:36px;box-shadow:var(--fsl-shadow-sm);">
          <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:2rem;margin-bottom:6px">{l s='Get in Touch' mod='fsl'}</h1>
          <p style="color:var(--fsl-gray-400);font-size:14px;margin-bottom:28px">{l s="We'd love to hear from you. Questions about plants, orders, or wholesale?" mod='fsl'}</p>

          <form method="post" action="{$urls.pages.contact}" enctype="multipart/form-data">
            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">

            <div class="form-group mb-3">
              <label>{l s='Subject' mod='fsl'}</label>
              <select class="form-control" name="id_contact">
                {foreach $contacts as $contact}
                  <option value="{$contact.id_contact|intval}">{$contact.name|escape:'htmlall':'UTF-8'}</option>
                {/foreach}
              </select>
            </div>

            {if $customer.email}
              <div class="form-group mb-3">
                <label>{l s='Your email' mod='fsl'}</label>
                <input type="email" class="form-control" name="from"
                       value="{$customer.email|escape:'htmlall':'UTF-8'}" required>
              </div>
            {/if}

            <div class="form-group mb-3">
              <label>{l s='Order reference (optional)' mod='fsl'}</label>
              <input type="text" class="form-control" name="id_order">
            </div>

            <div class="form-group mb-3">
              <label>{l s='Message' mod='fsl'}</label>
              <textarea class="form-control" name="message" rows="5" required
                        style="resize:vertical;min-height:120px;"></textarea>
            </div>

            <div class="form-group mb-4">
              <label>{l s='Attachment (optional)' mod='fsl'}</label>
              <input type="file" class="form-control" name="fileUpload" style="padding:8px;">
            </div>

            <button type="submit" name="submitMessage" class="btn btn-primary w-100">
              {l s='Send Message' mod='fsl'}
            </button>
          </form>
        </div>
      </div>

      <div class="col-lg-4">
        <div style="padding-top:16px;">
          <h4 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:20px">{l s='Other ways to reach us' mod='fsl'}</h4>
          <div class="d-flex flex-column gap-4">
            <div class="d-flex gap-3">
              <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">email</span>
              <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);margin:0 0 3px">Email</p>
                <a href="mailto:aphytoevolution@gmail.com" style="font-size:14px;color:var(--fsl-forest)">aphytoevolution@gmail.com</a>
              </div>
            </div>
            <div class="d-flex gap-3">
              <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">schedule</span>
              <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);margin:0 0 3px">{l s='Hours' mod='fsl'}</p>
                <p style="font-size:14px;color:var(--fsl-gray-600);margin:0">{l s='Mon–Sat, 9am–6pm IST' mod='fsl'}</p>
              </div>
            </div>
            <div class="d-flex gap-3">
              <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">local_shipping</span>
              <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);margin:0 0 3px">{l s='Dispatch' mod='fsl'}</p>
                <p style="font-size:14px;color:var(--fsl-gray-600);margin:0">{l s='Orders ship Mon, Wed & Fri' mod='fsl'}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
{/block}
