     /* ---------- Loading Screen ---------- */
    window.addEventListener('DOMContentLoaded', () => {
      // Simulate loading time (2 seconds)
      setTimeout(() => {
        const loadingScreen = document.getElementById('loading-screen');
        loadingScreen.classList.add('hidden');
        // Remove from DOM after transition
        setTimeout(() => {
          loadingScreen.style.display = 'none';
        }, 500);
      }, 2000);
    });

    /* ---------- Province mapping ---------- */
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
        B: "G2",
        C: "G3",
        D: "G4",
        E: "G5",
        F: "G6",
        G: "G7",
        H: "G8",
        I: "G9",
        J: "G1 — reste Estuaire"
      }
    };

    function getProvinceFromLogin(login){
      if(!login) return "Inconnu";
      const s = login.toUpperCase().trim();
      const keys = Object.keys(PROVINCE_RULES.prefixes).sort((a,b)=>b.length-a.length);
      for(let k of keys){ if(s.startsWith(k)) return PROVINCE_RULES.prefixes[k]; }
      const first = s[0];
      if(PROVINCE_RULES.letterMap[first]) return PROVINCE_RULES.letterMap[first];
      return "Inconnu";
    }

    /* ---------- Priority mapping ---------- */
    function getPriorityTag(type){
      const p1 = ["Non accès aux questionnaires","Non accès à l'application","Non accès à la synchronisation des données","Non accès à la synchronisation des paramètres"];
      const p2 = ["Non visibilité du personnel","Non accès aux zones de travail"];
      const p3 = ["Non accès aux outils de contrôle"];
      if(p1.includes(type)) return "P1";
      if(p2.includes(type)) return "P2";
      if(p3.includes(type)) return "P3";
      return "P4";
    }

    /* ---------- Storage ---------- */
    const KEY = "tickets_db_v1";
    const FAQ_KEY = "faq_db_v1";
    function loadTickets(){ try{ return JSON.parse(localStorage.getItem(KEY) || "[]"); }catch(e){return []} }
    function loadFAQ(){ try{ return JSON.parse(localStorage.getItem(FAQ_KEY) || "[]"); }catch(e){return []} }
    function saveTickets(list){ localStorage.setItem(KEY, JSON.stringify(list)); }

    function createTicketObj({reporterName, login, phone, type, pbTitle, desc, images=[], occurrences=1, province=null, status="open", assignee=null}){
      return {
        id: "T" + Date.now().toString(36).toUpperCase().slice(-6),
        title: type, //Replace here by the var pbTitle
        reporterName, login, phone, type, pbTitle, desc,
        images,
        occurrences,
        province: province || getProvinceFromLogin(login),
        priority: getPriorityTag(type),
        status, assignee,
        firstReportedAt: new Date().toISOString(),
        lastUpdatedAt: new Date().toISOString()
      };
    }

    /* ---------- DOM refs ---------- */
    const detectedProvinceEl = document.getElementById("detected-province");
    const reporterLoginInput = document.getElementById("reporter-login");
    const reporterNameInput = document.getElementById("reporter-name");
    const reporterPhoneInput = document.getElementById("reporter-phone");
    const typeSelect = document.getElementById("problem-type");
    const descInput = document.getElementById("problem-desc");
    const imagesInput = document.getElementById("images-input");
    const imagesPreview = document.getElementById("images-preview");
    const similarList = document.getElementById("similar-list");
    const successMessage = document.getElementById("success-message");
    const problemTitle = document.getElementById("problem-title");

    /* ---------- Live province detection ---------- */
    reporterLoginInput.addEventListener("input", ()=> {
      const val = reporterLoginInput.value.trim();
      detectedProvinceEl.textContent = val ? getProvinceFromLogin(val) : "—";
      renderSimilar(val);
    });

    /* ---------- Image preview ---------- */
    imagesInput.addEventListener("change", async (e)=> {
      imagesPreview.innerHTML = "";
      const files = Array.from(e.target.files).slice(0,6);
      for(const f of files){
        if(f.size > 5*1024*1024) {
          alert(`L'image ${f.name} dépasse 5MB et sera ignorée`);
          continue;
        }
        const url = await toDataURL(f);
        const div = document.createElement("div"); 
        div.className="img-thumb";
        const img = document.createElement("img"); 
        img.src = url; 
        img.style.width="100%"; 
        img.style.height="100%"; 
        img.style.objectFit="cover";
        div.appendChild(img); 
        imagesPreview.appendChild(div);
      }
    });

    function toDataURL(file){ 
      return new Promise(res=>{ 
        const r=new FileReader(); 
        r.onload=()=>res(r.result); 
        r.readAsDataURL(file); 
      })
    }

    /* ---------- Submit button ---------- */
    document.getElementById("btn-submit").addEventListener("click", async ()=>{
      const reporterName = reporterNameInput.value.trim();
      const login = reporterLoginInput.value.trim();
      const phone = reporterPhoneInput.value.trim();
      const type = typeSelect.value;
      const desc = descInput.value.trim();
      const pbTitle = problemTitle.value.trim();
      
      if(!reporterName || !login || !phone || !desc || !pbTitle){ 
        alert("⚠️ Veuillez remplir tous les champs obligatoires (*)"); 
        return; 
      }

      // Read images
      const images = [];
      if(imagesInput.files && imagesInput.files.length){
        for(const f of imagesInput.files){ 
          if(f.size<=5*1024*1024) images.push(await toDataURL(f)); 
        }
      }

      const ticket = createTicketObj({reporterName, login, phone, type, pbTitle, desc, images});
      const list = loadTickets();
      
      // Check for duplicate
      const existing = list.find(t=> 
        t.title===ticket.title && 
        t.province===ticket.province && 
        t.pbTitle===ticket.pbTitle &&
        t.desc.substring(0,30)===desc.substring(0,30)
      );
      
      if(existing){
        existing.occurrences = (existing.occurrences || 1) + 1;
        existing.lastUpdatedAt = new Date().toISOString();
        saveTickets(list);
        alert(`ℹ️ Un ticket similaire existe déjà (${existing.id}).\nLe compteur d'occurrences a été incrémenté.`);
      } else {
        list.unshift(ticket);
        saveTickets(list);
        successMessage.style.display = "block";
        setTimeout(()=>{ successMessage.style.display = "none"; }, 5000);
      }
      
      // Reset form
      reporterNameInput.value = "";
      reporterLoginInput.value = "";
      reporterPhoneInput.value = "";
      descInput.value = "";
      imagesInput.value = "";
      imagesPreview.innerHTML = "";
      detectedProvinceEl.textContent = "—";
      renderSimilar("");
    });

    /* ---------- Reset button ---------- */
    document.getElementById("btn-reset").addEventListener("click", ()=>{
      reporterNameInput.value = "";
      reporterLoginInput.value = "";
      reporterPhoneInput.value = "";
      descInput.value = "";
      imagesInput.value = "";
      imagesPreview.innerHTML = "";
      detectedProvinceEl.textContent = "—";
      renderSimilar("");
    });

    /* ---------- Render similar tickets ---------- */
    function renderSimilar(login){
      const list = loadTickets();
      const province = getProvinceFromLogin(login || "");
      const filtered = list.filter(t => t.province === province).slice(0,5);
      
      similarList.innerHTML = filtered.map(t=>`
        <div style="padding:10px;border-radius:8px;background:#fbfdff;margin-bottom:8px;border:1px solid #eef2f6">
          <div style="font-weight:600;margin-bottom:4px">${escapeHtml(t.title)} — ${t.id}</div>
          <div class="small muted">Déclaré par: ${escapeHtml(t.login)} • ${t.occurrences||1} occurrence(s) • ${t.status==='resolved'?'✅ Résolu':'🔄 Ouvert'}</div>
          <div class="small muted" style="margin-top:4px">${escapeHtml(t.desc.substring(0,80))}${t.desc.length>80?'...':''}</div>
        </div>
      `).join('') || '<div class="small muted" style="padding:10px;text-align:center">Aucun ticket similaire trouvé pour cette province</div>';
    }

    /* ---------- Utility ---------- */
    function escapeHtml(s){ 
      if(!s) return ''; 
      return s.replace(/[&<>"']/g, function(m){ 
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; 
      }); 
    }


        /* ---------- Render FAQ ---------- */
    function renderFAQ(){
      const faqList = loadFAQ();
      const faqSection = document.getElementById('faq-section');
      
      if(!faqList || faqList.length === 0){
        faqSection.innerHTML = '<div class="small muted" style="text-align:center;padding:20px">Aucune FAQ disponible pour le moment</div>';
        return;
      }else
        console.log("OK")
      faqSection.innerHTML = faqList.map(faq => `
        <div class="faq-item" style="border-left:3px solid ${faq.status==='resolved'?'#16a34a':faq.status==='workaround'?'#f59e0b':'#0369a1'}">
          <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:4px">
            <strong>${escapeHtml(faq.title)}</strong>
            <span style="font-size:11px;padding:2px 6px;border-radius:4px;background:${faq.status==='resolved'?'rgba(16,185,129,0.1)':faq.status==='workaround'?'rgba(245,158,11,0.1)':'rgba(3,105,161,0.1)'};color:${faq.status==='resolved'?'#16a34a':faq.status==='workaround'?'#f59e0b':'#0369a1'}">
              ${faq.status==='resolved'?'✅ Résolu':faq.status==='workaround'?'⚠️ Temporaire':'ℹ️ Connu'}
            </span>
          </div>
          <div class="small muted">${escapeHtml(faq.solution)}</div>
        </div>
      `).join('');
    }

    /* ---------- Initial render ---------- */
    renderSimilar("");
    renderFAQ();