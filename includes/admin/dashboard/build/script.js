document.addEventListener("DOMContentLoaded",(function(){new class{constructor(){this.isEmpty(gutenaFormsDashboard)?console.log(" gutenaFormsDashboard not found"):(this.select_change_url(),this.confirm_delete(),this.read_entries_status_update(),this.goToEntryViewPage(),setTimeout((()=>{this.gutenaFormsModal(),this.accordions(),this.scrollToSectionFromUrl()}),500),this.makeDashboardVisible())}scrollToSectionFromUrl(){let e=window.location.hash;void 0!==e&&void 0!==document.querySelector(e)&&(location.hash="#",location.hash=e)}makeDashboardVisible(){document.getElementById("gutena-forms-dashboard-page").style.display="block"}accordions(){let e=document.querySelectorAll("#gutena-forms-dashboard-page .gf-accordions .gf-title-icon");if(0<e.length)for(let t=0;t<e.length;t++)e[t].addEventListener("click",(function(){let e=this.nextElementSibling;e.style.maxHeight?e.style.maxHeight=null:e.style.maxHeight=e.scrollHeight+"px"}))}hasClass(e,t){return(" "+e.className+" ").indexOf(" "+t+" ")>-1}isEmpty(e){return null==e||""===e}getParents(e,t){let n=[];for(;e.parentNode!==document.body;)e.matches(t)&&n.push(e),e=e.parentNode;return 0<n.length&&n[0]}select_change_url(){let e=document.querySelectorAll(".gutena-forms-dashboard .select-change-url");if(0<e.length)for(let t=0;t<e.length;t++)e[t].addEventListener("change",(function(){let e=this.getAttribute("url");null!=e&&(window.location=e+this.value)}))}read_entries_status_update(){let e=document.querySelectorAll(".gutena-forms-dashboard .quick-view-form-entry-unread");if(0<e.length&&"undefined"!=typeof gutenaFormsDashboard&&null!==gutenaFormsDashboard){let t=this;for(let n=0;n<e.length;n++)e[n].addEventListener("click",(function(){let e=this.getAttribute("entryid"),n=t.getParents(this,"tr"),o=!1===n?"":n.getAttribute("currentstatus");void 0!==o&&"unread"===o&&null!=e&&0<e&&fetch(gutenaFormsDashboard.ajax_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded",Accept:"application/json","X-WP-Nonce":gutenaFormsDashboard.nonce},body:new URLSearchParams({action:gutenaFormsDashboard.read_status_action,gfnonce:gutenaFormsDashboard.nonce,form_entry_id:e})}).then((e=>e.json())).then((e=>{!1!==n&&(n.classList.remove("unread"),n.classList.add("read"),n.setAttribute("currentstatus","read"))})).catch((e=>(console.error("Error:",e),!1)))}))}}confirm_delete(){let e=document.querySelectorAll(".gutena-forms-dashboard .gf-delete");if(0<e.length){let t=this;for(let n=0;n<e.length;n++)e[n].addEventListener("click",(function(e){e.preventDefault();let n=this.getAttribute("href");if(null!=n){let e=document.querySelector("#gutena-forms-entry-delete-modal");if(!t.isEmpty(e)){let o=e.querySelector(".gf-entry-delete-btn");t.isEmpty(o)||(o.setAttribute("href",n),e.style.display="block")}}}))}}gutenaFormsModal(){let e=document.querySelectorAll(".toplevel_page_gutena-forms .gutena-forms-modal-btn"),t=0,n=this;if(0<e.length)for(t=0;t<e.length;t++)e[t].addEventListener("click",(function(e){e.preventDefault();let t=this.getAttribute("modalid");if(null==t)return console.log("modal not found"),!1;let n=document.querySelector("#"+t);null!=n&&(n.style.display="block")}));let o=document.querySelectorAll(".gutena-forms-modal .gf-close-btn");if(0<o.length)for(t=0;t<o.length;t++)o[t].addEventListener("click",(function(e){e.preventDefault();let t=n.getParents(this,".gutena-forms-modal");!1!==t&&(t.style.display="none")}));let l=document.querySelectorAll(".gutena-forms-modal");0<l.length&&(window.onclick=function(e){for(t=0;t<l.length;t++)e.target==l[t]&&(l[t].style.display="none")});let r=document.querySelectorAll(".gutena-forms-modal .gf-action-btn");if(0<r.length)for(t=0;t<r.length;t++)r[t].addEventListener("click",(function(){let e=this.nextElementSibling;"none"===e.style.display?e.style.display="block":e.style.display="none"}))}goToEntryViewPage(){let e=document.querySelectorAll(".toplevel_page_gutena-forms .gutena-forms-dashboard  .entry tbody tr"),t=0,n=this;if(0<e.length)for(t=0;t<e.length;t++)e[t].addEventListener("click",(function(e){let t=e.target.getAttribute("data-colname");if(t=n.isEmpty(t)?"":t.toLowerCase(),""!==t&&!["status","action"].includes(t)){let t=this.getAttribute("entryid");n.isEmpty(t)||(e.preventDefault(),window.location=gutenaFormsDashboard.entry_view_url+t)}}))}}}));