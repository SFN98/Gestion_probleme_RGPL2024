
        /* ---------- Authentication Check ---------- */
    window.addEventListener('DOMContentLoaded', () => {
      const isLoggedIn = sessionStorage.getItem('isLoggedIn');
      const rememberMe = localStorage.getItem('rememberMe');
      
      // Redirect to login if not authenticated
      if (isLoggedIn !== 'true' && rememberMe !== 'true') {
        window.location.href = 'login.html';
        return;
      }

      // Display user info
      const username = sessionStorage.getItem('username');
      const userRole = sessionStorage.getItem('userRole');
      if (username) {
        const header = document.querySelector('header h1');
        header.innerHTML = `📊 Dashboard Administrateur <span style="font-size:14px;font-weight:400;color:var(--muted);margin-left:12px">• ${username} (${userRole})</span>`;
      }
    });

    /* ---------- Logout Function ---------- */
    function logout() {
      if (confirm('Voulez-vous vraiment vous déconnecter ?')) {
        sessionStorage.clear();
        // Keep remember me if checked
        const rememberMe = localStorage.getItem('rememberMe');
        if (rememberMe !== 'true') {
          localStorage.removeItem('savedUsername');
        }
        window.location.href = 'login.html';
      }
    }



    /* ---------- Province & Priority mapping ---------- */
    const PROVINCE_RULES = {
      prefixes: {
        "2XA": "Estuaire Libreville",
        "2AA": "Estuaire Libreville",
        "3AAA": "Estuaire Libreville",
        "4AABA": "Estuaire Libreville",
        "5CAABA": "Moyen Ogooué"
      },
      letterMap: {
        A: "G1 — Libreville/Akanda/Owendo",
        B: "G2", C: "G3", D: "G4", E: "G5", F: "G6",
        G: "G7", H: "G8", I: "G9", J: "G1 — reste Estuaire"
      }
    };

    function getProvinceFromLogin(login){
      if(!login) return "Inconnu";
      const s = login.toUpperCase().trim();
      const keys = Object.keys(PROVINCE_RULES.prefixes).sort((a,b)=>b.length-a.length);
      for(let k of keys){ if(s.startsWith(k)) return PROVINCE_RULES.prefixes[k]; }
      const first = s[0];
      return PROVINCE_RULES.letterMap[first] || "Inconnu";
    }

    /* ---------- Storage ---------- */
    const KEY = "tickets_db_v1";
    const FAQ_KEY = "faq_db_v1";
    function loadTickets(){ try{ return JSON.parse(localStorage.getItem(KEY) || "[]"); }catch(e){return []} }
    function saveTickets(list){ localStorage.setItem(KEY, JSON.stringify(list)); renderAll(); }
    function loadFAQ(){ try{ return JSON.parse(localStorage.getItem(FAQ_KEY) || "[]"); }catch(e){return []} }
    function saveFAQ(list){ localStorage.setItem(FAQ_KEY, JSON.stringify(list)); renderFAQ(list); }

    /* ---------- State ---------- */
    let currentFilter = {status: '', priority: '', province: '', type: '', search: ''};
    let currentFaqId = null; // For editing FAQ

    /* ---------- DOM refs ---------- */
    const kTotal = document.getElementById("k-total");
    const kOpen = document.getElementById("k-open");
    const kResolved = document.getElementById("k-resolved");
    const kProgress = document.getElementById("k-progress");
    const searchInput = document.getElementById("search-input");
    const filterProvince = document.getElementById("filter-province");
    const filterType = document.getElementById("filter-type");
    const ticketsTableBody = document.querySelector("#tickets-table tbody");
    const noResults = document.getElementById("no-results");
    const ticketPreview = document.getElementById("ticket-preview");
    const provinceStats = document.getElementById("province-stats");
    const faqList = document.getElementById("faq-list");

    /* ---------- Render ---------- */
    function renderAll(){
      const list = loadTickets();
      
      // KPIs
      kTotal.textContent = list.length;
      kOpen.textContent = list.filter(t=>t.status==="open").length;
      kResolved.textContent = list.filter(t=>t.status==="resolved").length;
      kProgress.textContent = list.filter(t=>t.status==="progress").length;

      // Populate filters
      const provinces = [...new Set(list.map(t=>t.province))].sort();
      filterProvince.innerHTML = '<option value="">Toutes les provinces</option>' + 
        provinces.map(p=>`<option value="${p}">${p}</option>`).join("");
      
      const types = [...new Set(list.map(t=>t.type))].sort();
      filterType.innerHTML = '<option value="">Tous les types</option>' + 
        types.map(t=>`<option value="${t}">${t}</option>`).join("");

      // Render table
      renderTable(list);

      // Province stats
      renderProvinceStats(list);

      // FAQ
      const faqList = loadFAQ();
      renderFAQ(faqList);
    }

    function renderTable(list){
      let filtered = list.filter(t=>{
        if(currentFilter.status && t.status !== currentFilter.status) return false;
        if(currentFilter.priority && t.priority !== currentFilter.priority) return false;
        if(currentFilter.province && t.province !== currentFilter.province) return false;
        if(currentFilter.type && t.type !== currentFilter.type) return false;
        if(currentFilter.search){
          const s = currentFilter.search.toLowerCase();
          return t.id.toLowerCase().includes(s) || 
                 t.login.toLowerCase().includes(s) || 
                 (t.phone||'').toLowerCase().includes(s) || 
                 (t.desc||'').toLowerCase().includes(s) ||
                 (t.title||'').toLowerCase().includes(s) ||
                 (t.pbTitle||'').toLowerCase().includes(s);
        }
        return true;
      });

      const tbody = ticketsTableBody;
      tbody.innerHTML = "";

      if(filtered.length === 0){
        noResults.style.display = "block";
        document.querySelector("#tickets-table").style.display = "none";
        return;
      }

      noResults.style.display = "none";
      document.querySelector("#tickets-table").style.display = "table";

      for(const t of filtered){
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td style="font-weight:600">${t.id}</td>
          <td>${escapeHtml(t.pbTitle)}</td>
          <td>${escapeHtml(t.province)}</td>
          <td><strong>${t.occurrences||1}</strong></td>
          <td><span class="badge ${t.priority==='P1'?'p1':t.priority==='P2'?'p2':t.priority==='P3'?'p3':'p4'}">${t.priority}</span></td>
          <td>${escapeHtml(t.assignee || '-')}</td>
          <td>${t.status==='resolved'?'<span class="status-resolved">✅ Résolu</span>':
                t.status==='progress'?'<span style="color:var(--blue);font-weight:600">🔄 En cours</span>':
                '<span class="status-open">🔴 Ouvert</span>'}</td>
          <td class="actions">
            <button class="btn ghost" data-action="view" data-id="${t.id}">👁️ Voir</button>
            <button class="btn ghost" data-action="reassign" data-id="${t.id}">👤 Réaffecter</button>
            ${t.status!=='resolved'?`<button class="btn primary" data-action="resolve" data-id="${t.id}">✅ Résolu</button>`:''}
          </td>
        `;
        tbody.appendChild(tr);
      }
    }

    /* ---------- Table actions ---------- */
    ticketsTableBody.addEventListener("click", (e)=>{
      const btn = e.target.closest("button[data-action]");
      if(!btn) return;
      const action = btn.dataset.action;
      const id = btn.dataset.id;
      if(action==="view") showTicketDetails(id);
      if(action==="reassign") openReassignModal(id);
      if(action==="resolve") markResolved(id);
    });

    function showTicketDetails(id){
      const t = loadTickets().find(x=>x.id===id);
      if(!t) return;
      
      const content = `
        <div style="padding:4px">
          <div style="display:flex;justify-content:space-between;margin-bottom:12px">
            <div>
              <div style="font-weight:700;font-size:18px;margin-bottom:4px">${escapeHtml(t.pbTitle)}</div>
              <div class="small muted">ID: ${t.id} • ${t.status==='resolved'?'✅ Résolu':t.status==='progress'?'🔄 En cours':'🔴 Ouvert'}</div>
            </div>
            <span class="badge ${t.priority==='P1'?'p1':t.priority==='P2'?'p2':t.priority==='P3'?'p3':'p4'}">${t.priority}</span>
          </div>
          
          <div style="background:#f9fafb;padding:12px;border-radius:8px;margin-bottom:12px">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
              <div><strong>Soumetteur:</strong> ${escapeHtml(t.reporterName)}</div>
              <div><strong>Login:</strong> ${escapeHtml(t.login)}</div>
              <div><strong>Téléphone:</strong> ${escapeHtml(t.phone)}</div>
              <div><strong>Province:</strong> ${escapeHtml(t.province)}</div>
              <div><strong>Occurrences:</strong> ${t.occurrences||1} fois</div>
              <div><strong>Assigné à:</strong> ${escapeHtml(t.assignee || '-')}</div>
              <div><strong>Type de problème:</strong> ${escapeHtml(t.type)}</div>
            </div>
          </div>

          <div style="margin-bottom:12px">

            <strong>Description:</strong>
            <div style="margin-top:6px;padding:10px;background:#f9fafb;border-radius:6px">
              ${escapeHtml(t.desc)}
            </div>
          </div>

          ${(t.images && t.images.length) ? `
            <div style="margin-bottom:12px">
              <strong>Images jointes (${t.images.length}):</strong>
              <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:8px;margin-top:8px">
                ${t.images.map(src=>`
                  <div style="border-radius:8px;overflow:hidden;aspect-ratio:4/3">
                    <img src="${src}" style="width:100%;height:100%;object-fit:cover;cursor:pointer" onclick="window.open('${src}')">
                  </div>
                `).join('')}
              </div>
            </div>
          ` : ''}

          <div style="font-size:12px;color:var(--muted);margin-top:12px;padding-top:12px;border-top:1px solid #e5e7eb">
            <div>Créé: ${new Date(t.firstReportedAt).toLocaleString('fr-FR')}</div>
            <div>Dernière MAJ: ${new Date(t.lastUpdatedAt).toLocaleString('fr-FR')}</div>
          </div>
        </div>
      `;
      
      document.getElementById("modal-content").innerHTML = content;
      document.getElementById("modal-details").style.display = "flex";
    }

    /* ---------- Reassign modal ---------- */
    let currentModalTicket = null;
    function openReassignModal(id){
      currentModalTicket = id;
      document.getElementById("modal-ticket-id").textContent = id;
      document.getElementById("modal-assignee").value = "";
      document.getElementById("modal-reassign").style.display = "flex";
    }

    document.getElementById("modal-close-reassign").addEventListener("click", ()=>{
      document.getElementById("modal-reassign").style.display = "none";
      currentModalTicket = null;
    });

    document.getElementById("modal-save-reassign").addEventListener("click", ()=>{
      const name = document.getElementById("modal-assignee").value.trim();
      if(!name){ alert("⚠️ Entrez le nom du nouvel assigné"); return; }
      const list = loadTickets();
      const t = list.find(x=>x.id===currentModalTicket);
      if(!t){ alert("❌ Ticket introuvable"); return; }
      t.assignee = name;
      t.status = "progress";
      t.lastUpdatedAt = new Date().toISOString();
      saveTickets(list);
      document.getElementById("modal-reassign").style.display = "none";
      alert(`✅ Ticket ${t.id} réaffecté à ${name}`);
    });

    /* ---------- Mark resolved ---------- */
    function markResolved(id){
      if(!confirm("Marquer ce ticket comme résolu ?")) return;
      const list = loadTickets();
      const t = list.find(x=>x.id===id);
      if(!t) return;
      t.status = "resolved";
      t.lastUpdatedAt = new Date().toISOString();
      saveTickets(list);
      alert("✅ Ticket marqué comme résolu");
    }

    /* ---------- Modal details close ---------- */
    document.getElementById("modal-close-details").addEventListener("click", ()=>{
      document.getElementById("modal-details").style.display = "none";
    });

    /* ---------- Quick preview in sidebar ---------- */
    function showQuickPreview(id){
      const t = loadTickets().find(x=>x.id===id);
      if(!t){ 
        ticketPreview.innerHTML = '<div class="small muted">Sélectionnez un ticket pour voir les détails.</div>';
        return; 
      }
      ticketPreview.innerHTML = `
        <div><strong>${escapeHtml(t.title)}</strong> — ${t.id}</div>
        <div class="small muted" style="margin-top:4px">Par: ${escapeHtml(t.reporterName)} (${escapeHtml(t.login)})</div>
        <div class="small" style="margin-top:8px">${escapeHtml(t.desc.substring(0,150))}${t.desc.length>150?'...':''}</div>
        <div style="margin-top:8px"><span class="badge ${t.priority==='P1'?'p1':t.priority==='P2'?'p2':'p3'}">${t.priority}</span></div>
      `;
    }

    /* ---------- Province stats ---------- */
    function renderProvinceStats(list){
      const map = {};
      for(const t of list){ map[t.province] = (map[t.province]||0)+1; }
      const sorted = Object.entries(map).sort((a,b)=>b[1]-a[1]);
      provinceStats.innerHTML = sorted.map(([k,v])=>`
        <div class="province-stat">
          <span style="font-weight:500">${escapeHtml(k)}</span>
          <span style="background:var(--blue);color:#fff;padding:4px 10px;border-radius:6px;font-weight:600">${v}</span>
        </div>
      `).join('') || '<div class="small muted">Aucune donnée</div>';
    }

    /* ---------- FAQ ---------- */
      function renderFAQ(faqList){
      if(!faqList || faqList.length === 0){
        faqList.innerHTML = '<div class="small muted">Aucune FAQ disponible. Cliquez sur "Ajouter" pour créer.</div>';
        return;
      }
      
      faqList.innerHTML = faqList.map(faq=>`
        <div class="faq-item" style="border-left:3px solid ${faq.status==='resolved'?'var(--green)':faq.status==='workaround'?'var(--orange)':'var(--blue)'}">
          <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:6px">
            <strong style="flex:1">${escapeHtml(faq.title)}</strong>
            <div style="display:flex;gap:4px">
              <button class="btn ghost" data-action="edit-faq" data-id="${faq.id}" style="padding:4px 8px;font-size:11px">✏️</button>
              <button class="btn ghost" data-action="delete-faq" data-id="${faq.id}" style="padding:4px 8px;font-size:11px;color:var(--red)">🗑️</button>
            </div>
          </div>
          <div class="small muted">${escapeHtml(faq.solution)}</div>
          <div class="small" style="margin-top:6px;color:${faq.status==='resolved'?'var(--green)':faq.status==='workaround'?'var(--orange)':'var(--blue)'}">
            ${faq.status==='resolved'?'✅ Résolu':faq.status==='workaround'?'⚠️ Solution temporaire':'ℹ️ Problème connu'}
          </div>
        </div>
      `).join('') || '<div class="small muted">Aucune FAQ disponible</div>';
    }

    
    /* ---------- FAQ Management ---------- */
    const modalFaq = document.getElementById('modal-faq');
    const faqModalTitle = document.getElementById('faq-modal-title');
    const faqTitleInput = document.getElementById('faq-title');
    const faqSolutionInput = document.getElementById('faq-solution');
    const faqStatusSelect = document.getElementById('faq-status');

     // Open add FAQ modal
    document.getElementById('btn-add-faq').addEventListener('click', ()=>{
      currentFaqId = null;
      faqModalTitle.textContent = 'Ajouter une FAQ';
      faqTitleInput.value = '';
      faqSolutionInput.value = '';
      faqStatusSelect.value = 'resolved';
      modalFaq.style.display = 'flex';
    });

        // Close FAQ modal
    document.getElementById('modal-close-faq').addEventListener('click', ()=>{
      modalFaq.style.display = 'none';
      currentFaqId = null;
    });


    // Save FAQ
    document.getElementById('modal-save-faq').addEventListener('click', ()=>{
      const title = faqTitleInput.value.trim();
      const solution = faqSolutionInput.value.trim();
      const status = faqStatusSelect.value;

      if(!title || !solution){
        alert('⚠️ Veuillez remplir tous les champs obligatoires');
        return;
      }

      const faqList = loadFAQ();

      if(currentFaqId){
        // Edit existing
        const faq = faqList.find(f => f.id === currentFaqId);
        if(faq){
          faq.title = title;
          faq.solution = solution;
          faq.status = status;
          faq.updatedAt = new Date().toISOString();
        }
        alert('✅ FAQ modifiée avec succès');
      } else {
        // Add new
        const newFaq = {
          id: 'faq_' + Date.now(),
          title,
          solution,
          status,
          createdAt: new Date().toISOString()
        };
        faqList.push(newFaq);
        alert('✅ FAQ ajoutée avec succès');
      }

      saveFAQ(faqList);
      modalFaq.style.display = 'none';
      currentFaqId = null;
    });


    


    /* ---------- Filters ---------- */
    searchInput.addEventListener("input", ()=>{ 
      currentFilter.search = searchInput.value.trim(); 
      renderAll(); 
    });
    
    filterProvince.addEventListener("change", ()=>{ 
      currentFilter.province = filterProvince.value; 
      renderAll(); 
    });
    
    filterType.addEventListener("change", ()=>{ 
      currentFilter.type = filterType.value; 
      renderAll(); 
    });

    document.getElementById("filter-all").addEventListener("click", ()=>{
      currentFilter = {status: '', priority: '', province: '', type: '', search: ''};
      searchInput.value = '';
      filterProvince.value = '';
      filterType.value = '';
      renderAll();
    });

    document.getElementById("filter-open").addEventListener("click", ()=>{
      currentFilter.status = 'open';
      renderAll();
    });

    document.getElementById("filter-resolved").addEventListener("click", ()=>{
      currentFilter.status = 'resolved';
      renderAll();
    });

    document.getElementById("filter-p1").addEventListener("click", ()=>{
      currentFilter.priority = 'P1';
      renderAll();
    });

    /* ---------- Clear data ---------- */
    document.getElementById("btn-clear-data").addEventListener("click", ()=>{
      if(confirm("⚠️ Supprimer TOUTES les données ? Cette action est irréversible.")){
        localStorage.removeItem(KEY);
        location.reload();
      }
    });

    /* ---------- Utility ---------- */
    function escapeHtml(s){ 
      if(!s) return ''; 
      return s.replace(/[&<>"']/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]); 
    }

    /* ---------- Init ---------- */
    renderAll();
