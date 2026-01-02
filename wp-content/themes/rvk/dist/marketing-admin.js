(function(a){let o=null;a(document).ready(function(){u(),d(),g()});function u(){a(".rvk-upload-image").on("click",function(t){t.preventDefault();const e=a(this),n=e.data("target"),l=a("#"+n),i=a("#"+n+"_preview"),r=e.siblings(".rvk-remove-image"),s=wp.media({title:"Odaberi sliku",button:{text:"Koristi ovu sliku"},multiple:!1});s.on("select",function(){const m=s.state().get("selection").first().toJSON();l.val(m.id),i.html('<img src="'+m.url+'" style="max-width: 300px; height: auto;">'),r.show()}),s.open()}),a(".rvk-remove-image").on("click",function(t){t.preventDefault();const e=a(this),n=e.data("target"),l=a("#"+n),i=a("#"+n+"_preview");l.val(""),i.html(""),e.hide()}),a(document).on("click",".rvk-upload-small-banner",function(t){t.preventDefault();const n=a(this).closest(".small-banner-item"),l=n.find(".small-banner-image-id"),i=n.find(".small-banner-preview"),r=wp.media({title:"Odaberi sliku",button:{text:"Koristi ovu sliku"},multiple:!1});r.on("select",function(){const s=r.state().get("selection").first().toJSON();l.val(s.id),i.html('<img src="'+s.url+'" style="max-width: 150px; height: auto;">')}),r.open()})}function d(){const t=document.getElementById("small-banners-container");t&&(t.addEventListener("dragstart",function(e){e.target.classList.contains("small-banner-item")&&(o=e.target,e.target.style.opacity="0.5")}),t.addEventListener("dragend",function(e){e.target.classList.contains("small-banner-item")&&(e.target.style.opacity="1",c())}),t.addEventListener("dragover",function(e){e.preventDefault();const n=b(t,e.clientY),l=o;n==null?t.appendChild(l):t.insertBefore(l,n)}))}function b(t,e){return[...t.querySelectorAll(".small-banner-item:not(.dragging)")].reduce((l,i)=>{const r=i.getBoundingClientRect(),s=e-r.top-r.height/2;return s<0&&s>l.offset?{offset:s,element:i}:l},{offset:Number.NEGATIVE_INFINITY}).element}function c(){document.querySelectorAll(".small-banner-item").forEach((e,n)=>{const l=e.querySelector(".small-banner-image-id"),i=e.querySelector('input[type="url"]'),r=e.querySelector('input[type="text"]');l&&(l.name=`rvk_marketing_banners[small_banners][${n}][image]`),i&&(i.name=`rvk_marketing_banners[small_banners][${n}][link]`),r&&(r.name=`rvk_marketing_banners[small_banners][${n}][alt_text]`)})}function g(){a("#add-small-banner").on("click",function(){const t=a("#small-banners-container"),e=t.find(".small-banner-item").length,n=`
                <div class="small-banner-item" draggable="true">
                    <div class="small-banner-drag-handle">☰</div>
                    <div class="small-banner-content">
                        <div class="rvk-image-upload">
                            <input type="hidden" 
                                   name="rvk_marketing_banners[small_banners][${e}][image]" 
                                   class="small-banner-image-id" 
                                   value="">
                            <button type="button" class="button rvk-upload-small-banner">
                                Odaberi sliku
                            </button>
                            <div class="rvk-image-preview small-banner-preview"></div>
                        </div>
                        <div class="small-banner-link-field">
                            <label>Link:</label>
                            <input type="url" 
                                   name="rvk_marketing_banners[small_banners][${e}][link]" 
                                   value="" 
                                   class="regular-text"
                                   placeholder="https://example.com">
                        </div>
                        <div class="small-banner-link-field">
                            <label>Alt Tekst:</label>
                            <input type="text" 
                                   name="rvk_marketing_banners[small_banners][${e}][alt_text]" 
                                   value="" 
                                   class="regular-text"
                                   placeholder="Opis bannera">
                        </div>
                        <button type="button" class="button button-link-delete rvk-remove-small-banner">Ukloni</button>
                    </div>
                </div>
            `;t.append(n)}),a(document).on("click",".rvk-remove-small-banner",function(){a(this).closest(".small-banner-item").remove(),c()})}})(jQuery);
