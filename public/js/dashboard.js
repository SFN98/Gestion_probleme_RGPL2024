(function () {
  'use strict';

  const appBase = window.appBase || '';
  const currentUsername = window.currentUsername || '';
  
  // Données globales
  const ticketsData = window.ticketsData || [];
  const faqData = window.faqData || [];
  const pdfData = window.pdfData || [];

  function showLoader() {
    if (window.appLoaderWrapper) {
      window.appLoaderWrapper.style.display = 'block';
    }
  }

  function hideLoader() {
    if (window.appLoaderWrapper) {
      window.appLoaderWrapper.style.display = 'none';
    }
  }

  function showError(message) {
    if (window.showAlertModal) {
      window.showAlertModal('Erreur : ' + message, 'error');
    } else {
      alert('Erreur : ' + message);
    }
  }

  function refreshPage() {
    window.location.reload();
  }

  // Fetch avec timeout et retry
  function fetchWithTimeout(url, options, timeout = 30000) {
    return new Promise(function (resolve, reject) {
      const controller = new AbortController();
      const timeoutId = setTimeout(function () {
        controller.abort();
      }, timeout);

      fetch(url, { ...options, signal: controller.signal })
        .then(function (response) {
          clearTimeout(timeoutId);
          resolve(response);
        })
        .catch(function (error) {
          clearTimeout(timeoutId);
          if (error.name === 'AbortError') {
            if (window.showConfirmModal) {
              window.showConfirmModal('Réseau lent ou indisponible. Réessayer ?', function () {
                fetchWithTimeout(url, options, timeout).then(resolve).catch(reject);
              }, function () {
                reject(new Error('Timeout : réseau trop lent'));
              });
            } else {
              if (confirm('Réseau lent ou indisponible. Réessayer ?')) {
                return fetchWithTimeout(url, options, timeout).then(resolve).catch(reject);
              }
              reject(new Error('Timeout : réseau trop lent'));
            }
          } else {
            reject(error);
          }
        });
    });
  }

  // Prendre un ticket
  window.takeTicket = function (ticketId) {
    if (window.showConfirmModal) {
      window.showConfirmModal('Prendre ce ticket ?', function () {
        takeTicketAction(ticketId);
      });
    } else {
      if (!confirm('Prendre ce ticket ?')) return;
      takeTicketAction(ticketId);
    }
  };

  function takeTicketAction(ticketId) {
    showLoader();
    const formData = new FormData();
    formData.append('action', 'take');
    formData.append('ticketId', ticketId);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          refreshPage();
        } else {
          showError(data.error || 'Erreur lors de la prise du ticket');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  }

  // Libérer un ticket
  window.releaseTicket = function (ticketId) {
    if (window.showConfirmModal) {
      window.showConfirmModal('Libérer ce ticket ?', function () {
        releaseTicketAction(ticketId);
      });
    } else {
      if (!confirm('Libérer ce ticket ?')) return;
      releaseTicketAction(ticketId);
    }
  };

  function releaseTicketAction(ticketId) {
    showLoader();
    const formData = new FormData();
    formData.append('action', 'release');
    formData.append('ticketId', ticketId);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          refreshPage();
        } else {
          showError('Erreur lors de la libération');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  }

  // Afficher modal résolution
  window.showResolveModal = function (ticketId) {
    document.getElementById('resolve-ticket-id').value = ticketId;
    document.getElementById('resolve-solution-type').value = '';
    document.getElementById('resolve-response').value = '';
    document.getElementById('resolve-pdf-file').value = '';
    document.getElementById('resolve-video-file').value = '';
    document.getElementById('resolve-pdf-preview').style.display = 'none';
    document.getElementById('resolve-video-preview').style.display = 'none';
    toggleSolutionFields();
    document.getElementById('modal-resolve').style.display = 'flex';
  };

  // Afficher modal réaffectation
  window.showReassignModal = function (ticketId, currentAssignee) {
    document.getElementById('reassign-ticket-id').value = ticketId;
    document.getElementById('reassign-new-assignee').value = '';
    document.getElementById('modal-reassign').style.display = 'flex';
  };

  // Afficher détail ticket
  window.showTicketDetail = function (ticketId) {
    const ticket = ticketsData.find(t => t.id === ticketId);
    if (!ticket) {
      showError('Ticket non trouvé');
      return;
    }
    const baseUrl = window.baseUrl || '';
    const images = ticket.images || [];
    let imagesHtml = '';
    if (images.length > 0) {
      imagesHtml = '<p><strong>Pièces jointes :</strong></p><div class="images-preview">';
      images.forEach(function (img) {
        const isImage = /\.(jpg|jpeg|png|gif|webp)$/i.test(img);
        if (isImage) {
          imagesHtml += `<a href="${baseUrl}${escapeHtml(img)}" target="_blank" rel="noopener"><img src="${baseUrl}${escapeHtml(img)}" alt="Pièce jointe" class="img-thumb"></a>`;
        } else {
          const fileName = img.split('/').pop();
          imagesHtml += `<a href="${baseUrl}${escapeHtml(img)}" target="_blank" rel="noopener" class="btn btn--sm btn--primary">Télécharger ${escapeHtml(fileName)}</a>`;
        }
      });
      imagesHtml += '</div>';
    }
    const content = document.getElementById('ticket-detail-content');
    content.innerHTML = `
      <div class="ticket-detail-section">
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">ID :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.id)}</span>
          <button type="button" class="btn btn--sm btn--primary" onclick="copyTicketIdFromModal('${escapeHtml(ticket.id)}', this)" title="Copier le code">
            <i class="fas fa-copy"></i>
            <span>Copier</span>
          </button>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Titre :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.title)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Soumetteur :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.reporterName)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Login :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.login)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Téléphone :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.phone)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Type :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.type)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Province :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.province)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Priorité :</span>
          <span class="ticket-detail-value ticket-priority ticket-priority--${escapeHtml(ticket.priority.toLowerCase())}">${escapeHtml(ticket.priority)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Statut :</span>
          <span class="ticket-detail-value ticket-status ticket-status--${escapeHtml(ticket.status.toLowerCase())}">${escapeHtml(ticket.status)}</span>
        </div>
        <div class="ticket-detail-row">
          <span class="ticket-detail-label">Assigné :</span>
          <span class="ticket-detail-value">${escapeHtml(ticket.assignee || 'Non assigné')}</span>
        </div>
      </div>
      <div class="ticket-detail-section">
        <p class="ticket-detail-label"><strong>Description :</strong></p>
        <div class="ticket-detail-description">${escapeHtml(ticket.desc)}</div>
      </div>
      ${imagesHtml ? `<div class="ticket-detail-section">${imagesHtml}</div>` : ''}
      ${ticket.response ? `<div class="ticket-detail-section"><p class="ticket-detail-label"><strong>Réponse :</strong></p><div class="ticket-detail-description">${escapeHtml(ticket.response)}</div></div>` : ''}
    `;
    document.getElementById('modal-ticket-detail').style.display = 'flex';
  };

  // Copier le code du ticket depuis la modale
  window.copyTicketIdFromModal = function (ticketId, btnElement) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(ticketId).then(function () {
        const btn = btnElement || (window.event && window.event.target.closest('button'));
        if (btn) {
          const originalHtml = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i><span>Copié !</span>';
          btn.classList.add('btn--success');
          setTimeout(function () {
            btn.innerHTML = originalHtml;
            btn.classList.remove('btn--success');
          }, 2000);
        }
      }).catch(function () {
        fallbackCopyTicketId(ticketId, btnElement);
      });
    } else {
      fallbackCopyTicketId(ticketId, btnElement);
    }
  };

  function fallbackCopyTicketId(ticketId, btnElement) {
    const textarea = document.createElement('textarea');
    textarea.value = ticketId;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand('copy');
      const btn = btnElement || (window.event && window.event.target.closest('button'));
      if (btn) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i><span>Copié !</span>';
        btn.classList.add('btn--success');
        setTimeout(function () {
          btn.innerHTML = originalHtml;
          btn.classList.remove('btn--success');
        }, 2000);
      }
    } catch (err) {
      if (window.showAlertModal) {
        window.showAlertModal('Impossible de copier le code. Veuillez le copier manuellement.', 'error');
      } else {
        alert('Impossible de copier le code. Veuillez le copier manuellement.');
      }
    }
    document.body.removeChild(textarea);
  }

  // Fermer modale
  window.closeModal = function (modalId) {
    document.getElementById(modalId).style.display = 'none';
  };

  // Fonctions pour gérer les champs de solution
  window.toggleSolutionFields = function () {
    const solutionType = document.getElementById('resolve-solution-type').value;
    const textGroup = document.getElementById('resolve-text-group');
    const pdfGroup = document.getElementById('resolve-pdf-group');
    const videoGroup = document.getElementById('resolve-video-group');
    const responseField = document.getElementById('resolve-response');
    const pdfFileField = document.getElementById('resolve-pdf-file');
    const videoFileField = document.getElementById('resolve-video-file');
    
    // Masquer tous les groupes
    textGroup.style.display = 'none';
    pdfGroup.style.display = 'none';
    videoGroup.style.display = 'none';
    
    // Réinitialiser les champs requis
    responseField.removeAttribute('required');
    pdfFileField.removeAttribute('required');
    videoFileField.removeAttribute('required');
    
    // Afficher le groupe approprié
    if (solutionType === 'text') {
      textGroup.style.display = 'block';
      responseField.setAttribute('required', 'required');
    } else if (solutionType === 'pdf') {
      pdfGroup.style.display = 'block';
      pdfFileField.setAttribute('required', 'required');
    } else if (solutionType === 'video') {
      videoGroup.style.display = 'block';
      videoFileField.setAttribute('required', 'required');
    }
  };

  window.handlePdfFileSelect = function (input) {
    if (input.files && input.files.length > 0) {
      const fileName = input.files[0].name;
      document.getElementById('resolve-pdf-name').textContent = fileName;
      document.getElementById('resolve-pdf-preview').style.display = 'block';
    }
  };

  window.clearPdfFile = function () {
    document.getElementById('resolve-pdf-file').value = '';
    document.getElementById('resolve-pdf-preview').style.display = 'none';
  };

  window.handleVideoFileSelect = function (input) {
    if (input.files && input.files.length > 0) {
      const fileName = input.files[0].name;
      document.getElementById('resolve-video-name').textContent = fileName;
      document.getElementById('resolve-video-preview').style.display = 'block';
    }
  };

  window.clearVideoFile = function () {
    document.getElementById('resolve-video-file').value = '';
    document.getElementById('resolve-video-preview').style.display = 'none';
  };

  // Formulaires modales
  document.getElementById('form-resolve')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const ticketId = document.getElementById('resolve-ticket-id').value;
    const solutionType = document.getElementById('resolve-solution-type').value;
    const solutionContent = document.getElementById('resolve-response').value.trim();
    const pdfFile = document.getElementById('resolve-pdf-file').files[0];
    const videoFile = document.getElementById('resolve-video-file').files[0];
    
    if (!solutionType) {
      showError('Veuillez sélectionner un type de solution');
      return;
    }
    
    if (solutionType === 'text' && !solutionContent) {
      showError('Veuillez saisir une explication');
      return;
    }
    
    if (solutionType === 'pdf' && !pdfFile) {
      showError('Veuillez sélectionner un fichier PDF');
      return;
    }
    
    if (solutionType === 'video' && !videoFile) {
      showError('Veuillez sélectionner un fichier vidéo');
      return;
    }
    
    showLoader();
    const formData = new FormData();
    formData.append('action', 'resolve');
    formData.append('ticketId', ticketId);
    formData.append('solutionType', solutionType);
    formData.append('solutionContent', solutionContent);
    
    if (solutionType === 'pdf' && pdfFile) {
      formData.append('solutionFile', pdfFile);
    } else if (solutionType === 'video' && videoFile) {
      formData.append('solutionFile', videoFile);
    }
    
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          closeModal('modal-resolve');
          refreshPage();
        } else {
          showError(data.error || 'Erreur lors de la résolution');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  });

  document.getElementById('form-reassign')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const ticketId = document.getElementById('reassign-ticket-id').value;
    const newAssignee = document.getElementById('reassign-new-assignee').value.trim();
    if (!newAssignee) {
      showError('Veuillez saisir un nom');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', 'reassign');
    formData.append('ticketId', ticketId);
    formData.append('newAssignee', newAssignee);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          closeModal('modal-reassign');
          refreshPage();
        } else {
          showError('Erreur lors de la réaffectation');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  });

  // FAQ
  window.showFaqModal = function (faqId) {
    const faq = faqId ? faqData.find(f => f.id === faqId) : null;
    document.getElementById('faq-id').value = faqId || '';
    document.getElementById('faq-title').value = faq ? (faq.title || '') : '';
    document.getElementById('faq-question').value = faq ? (faq.question || faq.title || '') : '';
    document.getElementById('faq-solution').value = faq ? (faq.solution || '') : '';
    document.getElementById('faq-status').value = faq ? (faq.status || 'resolved') : 'resolved';
    document.getElementById('modal-faq-title').textContent = faqId ? 'Modifier la FAQ' : 'Ajouter une FAQ';
    document.getElementById('modal-faq').style.display = 'flex';
  };

  document.getElementById('form-faq')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('faq-id').value;
    const title = document.getElementById('faq-title').value.trim();
    const question = document.getElementById('faq-question').value.trim();
    const solution = document.getElementById('faq-solution').value.trim();
    const status = document.getElementById('faq-status').value;
    if (!title || !question || !solution) {
      showError('Veuillez remplir tous les champs obligatoires');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', id ? 'faq_update' : 'faq_create');
    if (id) formData.append('id', id);
    formData.append('title', title);
    formData.append('category', title);
    formData.append('question', question);
    formData.append('solution', solution);
    formData.append('status', status);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          closeModal('modal-faq');
          refreshPage();
        } else {
          showError('Erreur lors de l\'enregistrement');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  });

  window.deleteFaq = function (faqId) {
    if (window.showConfirmModal) {
      window.showConfirmModal('Supprimer cette FAQ ?', function () {
        deleteFaqAction(faqId);
      });
    } else {
      if (!confirm('Supprimer cette FAQ ?')) return;
      deleteFaqAction(faqId);
    }
  };

  function deleteFaqAction(faqId) {
    showLoader();
    const formData = new FormData();
    formData.append('action', 'faq_delete');
    formData.append('id', faqId);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          refreshPage();
        } else {
          showError('Erreur lors de la suppression');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  }

  // PDF
  window.showPdfModal = function (pdfId) {
    const pdf = pdfId ? pdfData.find(p => p.id === pdfId) : null;
    const pdfFileInput = document.getElementById('pdf-file');
    const currentFileSpan = document.getElementById('pdf-current-file');
    document.getElementById('pdf-id').value = pdfId || '';
    document.getElementById('pdf-title').value = pdf ? pdf.title : '';
    document.getElementById('pdf-description').value = pdf ? pdf.description : '';
    pdfFileInput.value = '';
    if (pdf && pdf.fileUrl) {
      const fileName = pdf.fileUrl.split('/').pop();
      currentFileSpan.textContent = 'Fichier actuel : ' + fileName;
    } else {
      currentFileSpan.textContent = '';
    }
    document.getElementById('pdf-keywords').value = pdf && Array.isArray(pdf.keywords) ? pdf.keywords.join(', ') : (pdf ? pdf.keywords : '');
    document.getElementById('modal-pdf-title').textContent = pdfId ? 'Modifier le PDF' : 'Ajouter un PDF';
    document.getElementById('modal-pdf').style.display = 'flex';
  };

  document.getElementById('form-pdf')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('pdf-id').value;
    const title = document.getElementById('pdf-title').value.trim();
    const description = document.getElementById('pdf-description').value.trim();
    const pdfFileInput = document.getElementById('pdf-file');
    const keywordsStr = document.getElementById('pdf-keywords').value.trim();
    const keywords = keywordsStr ? keywordsStr.split(',').map(k => k.trim()).filter(k => k) : [];
    if (!title) {
      showError('Veuillez saisir un titre');
      return;
    }
    if (!id && (!pdfFileInput.files || pdfFileInput.files.length === 0)) {
      showError('Veuillez sélectionner un fichier PDF');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', id ? 'pdf_update' : 'pdf_create');
    if (id) formData.append('id', id);
    formData.append('title', title);
    formData.append('description', description);
    if (pdfFileInput.files && pdfFileInput.files.length > 0) {
      formData.append('pdf_file', pdfFileInput.files[0]);
    }
    formData.append('keywords', JSON.stringify(keywords));
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          closeModal('modal-pdf');
          refreshPage();
        } else {
          showError(data.error || 'Erreur lors de l\'enregistrement');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  });

  window.deletePdf = function (pdfId) {
    if (window.showConfirmModal) {
      window.showConfirmModal('Supprimer ce PDF ?', function () {
        deletePdfAction(pdfId);
      });
    } else {
      if (!confirm('Supprimer ce PDF ?')) return;
      deletePdfAction(pdfId);
    }
  };

  function deletePdfAction(pdfId) {
    showLoader();
    const formData = new FormData();
    formData.append('action', 'pdf_delete');
    formData.append('id', pdfId);
    fetchWithTimeout(appBase + '/dashboard', {
      method: 'POST',
      body: formData
    })
      .then(r => r.json())
      .then(data => {
        hideLoader();
        if (data.success) {
          refreshPage();
        } else {
          showError('Erreur lors de la suppression');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  }

  // Fermer modale au clic sur le fond
  document.querySelectorAll('.modal-back').forEach(back => {
    back.addEventListener('click', function (e) {
      if (e.target === back) {
        back.style.display = 'none';
      }
    });
  });

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();
