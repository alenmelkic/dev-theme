(function(){let c=null;document.readyState==="loading"?document.addEventListener("DOMContentLoaded",d):d();function d(){g(),p(),v()}function g(){document.querySelectorAll(".rvk-upload-image").forEach(function(t){t.addEventListener("click",function(n){n.preventDefault();const l=this.getAttribute("data-target"),r=document.getElementById(l),i=document.getElementById(l+"_preview"),s=this.parentNode.querySelector(".rvk-remove-image");if(!window.wp||!window.wp.media){alert("WordPress media uploader not available");return}const o=wp.media({title:"Odaberi sliku",button:{text:"Koristi ovu sliku"},multiple:!1});o.on("select",function(){const m=o.state().get("selection").first().toJSON();r.value=m.id,i.innerHTML='<img src="'+m.url+'" style="max-width: 300px; height: auto;">',s&&(s.style.display="inline-block")}),o.open()})}),document.querySelectorAll(".rvk-remove-image").forEach(function(t){t.addEventListener("click",function(n){n.preventDefault();const l=this.getAttribute("data-target"),r=document.getElementById(l),i=document.getElementById(l+"_preview");r.value="",i.innerHTML="",this.style.display="none"})}),document.addEventListener("click",function(t){if(t.target.classList.contains("rvk-upload-small-banner")){t.preventDefault();const l=t.target.closest(".small-banner-item"),r=l.querySelector(".small-banner-image-id"),i=l.querySelector(".small-banner-preview");if(!window.wp||!window.wp.media){alert("WordPress media uploader not available");return}const s=wp.media({title:"Odaberi sliku",button:{text:"Koristi ovu sliku"},multiple:!1});s.on("select",function(){const o=s.state().get("selection").first().toJSON();r.value=o.id,i.innerHTML='<img src="'+o.url+'" style="max-width: 150px; height: auto;">'}),s.open()}})}function p(){const a=document.getElementById("small-banners-container");a&&(a.addEventListener("dragstart",function(e){e.target.classList.contains("small-banner-item")&&(c=e.target,e.target.style.opacity="0.5")}),a.addEventListener("dragend",function(e){e.target.classList.contains("small-banner-item")&&(e.target.style.opacity="1",u())}),a.addEventListener("dragover",function(e){e.preventDefault();const t=b(a,e.clientY),n=c;t==null?a.appendChild(n):a.insertBefore(n,t)}))}function b(a,e){return Array.from(a.querySelectorAll(".small-banner-item:not(.dragging)")).reduce(function(n,l){const r=l.getBoundingClientRect(),i=e-r.top-r.height/2;return i<0&&i>n.offset?{offset:i,element:l}:n},{offset:Number.NEGATIVE_INFINITY}).element}function u(){document.querySelectorAll(".small-banner-item").forEach(function(e,t){const n=e.querySelector(".small-banner-image-id"),l=e.querySelector('input[type="url"]'),r=e.querySelector('input[type="text"]');n&&(n.name="rvk_marketing_banners[small_banners]["+t+"][image]"),l&&(l.name="rvk_marketing_banners[small_banners]["+t+"][link]"),r&&(r.name="rvk_marketing_banners[small_banners]["+t+"][alt_text]")})}function v(){const a=document.getElementById("add-small-banner");a&&(a.addEventListener("click",function(){const e=document.getElementById("small-banners-container"),t=e.querySelectorAll(".small-banner-item").length,n=document.createElement("div");n.className="small-banner-item",n.draggable=!0,n.innerHTML=`
                <div class="small-banner-drag-handle">☰</div>
                <div class="small-banner-content">
                    <div class="rvk-image-upload">
                        <input type="hidden"
                               name="rvk_marketing_banners[small_banners][${t}][image]"
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
                               name="rvk_marketing_banners[small_banners][${t}][link]"
                               value=""
                               class="regular-text"
                               placeholder="https://example.com">
                    </div>
                    <div class="small-banner-link-field">
                        <label>Alt Tekst:</label>
                        <input type="text"
                               name="rvk_marketing_banners[small_banners][${t}][alt_text]"
                               value=""
                               class="regular-text"
                               placeholder="Opis bannera">
                    </div>
                    <button type="button" class="button button-link-delete rvk-remove-small-banner">Ukloni</button>
                </div>
            `,e.appendChild(n)}),document.addEventListener("click",function(e){if(e.target.classList.contains("rvk-remove-small-banner")){const t=e.target.closest(".small-banner-item");t&&(t.remove(),u())}}))}})();
