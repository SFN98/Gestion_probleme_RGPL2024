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
    alert('Erreur : ' + message);
  }

  function refreshPage() {
    window.location.reload();
  }

  // Prendre un ticket
  window.takeTicket = function (ticketId) {
    if (!confirm('Prendre ce ticket ?')) return;
    showLoader();
    const formData = new FormData();
    formData.append('action', 'take');
    formData.append('ticketId', ticketId);
    fetch(appBase + '/dashboard', {
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
  };

  // Libérer un ticket
  window.releaseTicket = function (ticketId) {
    if (!confirm('Libérer ce ticket ?')) return;
    showLoader();
    const formData = new FormData();
    formData.append('action', 'release');
    formData.append('ticketId', ticketId);
    fetch(appBase + '/dashboard', {
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
  };

  // Afficher modal résolution
  window.showResolveModal = function (ticketId) {
    document.getElementById('resolve-ticket-id').value = ticketId;
    document.getElementById('resolve-response').value = '';
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
    const content = document.getElementById('ticket-detail-content');
    content.innerHTML = `
      <p><strong>ID :</strong> ${escapeHtml(ticket.id)}</p>
      <p><strong>Titre :</strong> ${escapeHtml(ticket.title)}</p>
      <p><strong>Soumetteur :</strong> ${escapeHtml(ticket.reporterName)}</p>
      <p><strong>Login :</strong> ${escapeHtml(ticket.login)}</p>
      <p><strong>Téléphone :</strong> ${escapeHtml(ticket.phone)}</p>
      <p><strong>Type :</strong> ${escapeHtml(ticket.type)}</p>
      <p><strong>Province :</strong> ${escapeHtml(ticket.province)}</p>
      <p><strong>Priorité :</strong> ${escapeHtml(ticket.priority)}</p>
      <p><strong>Statut :</strong> ${escapeHtml(ticket.status)}</p>
      <p><strong>Assigné :</strong> ${escapeHtml(ticket.assignee || 'Non assigné')}</p>
      <p><strong>Description :</strong></p>
      <div style="white-space: pre-wrap; background: var(--color-surface); padding: var(--space-3); border-radius: var(--radius-md);">${escapeHtml(ticket.desc)}</div>
      ${ticket.response ? `<p><strong>Réponse :</strong></p><div style="white-space: pre-wrap; background: var(--color-surface); padding: var(--space-3); border-radius: var(--radius-md);">${escapeHtml(ticket.response)}</div>` : ''}
    `;
    document.getElementById('modal-ticket-detail').style.display = 'flex';
  };

  // Fermer modale
  window.closeModal = function (modalId) {
    document.getElementById(modalId).style.display = 'none';
  };

  // Formulaires modales
  document.getElementById('form-resolve')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const ticketId = document.getElementById('resolve-ticket-id').value;
    const response = document.getElementById('resolve-response').value;
    if (!response.trim()) {
      showError('Veuillez saisir une réponse');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', 'resolve');
    formData.append('ticketId', ticketId);
    formData.append('response', response);
    fetch(appBase + '/dashboard', {
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
          showError('Erreur lors de la résolution');
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
    fetch(appBase + '/dashboard', {
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
    document.getElementById('faq-title').value = faq ? faq.title : '';
    document.getElementById('faq-solution').value = faq ? faq.solution : '';
    document.getElementById('faq-status').value = faq ? faq.status : 'resolved';
    document.getElementById('modal-faq-title').textContent = faqId ? 'Modifier la FAQ' : 'Ajouter une FAQ';
    document.getElementById('modal-faq').style.display = 'flex';
  };

  document.getElementById('form-faq')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('faq-id').value;
    const title = document.getElementById('faq-title').value.trim();
    const solution = document.getElementById('faq-solution').value.trim();
    const status = document.getElementById('faq-status').value;
    if (!title || !solution) {
      showError('Veuillez remplir tous les champs');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', id ? 'faq_update' : 'faq_create');
    if (id) formData.append('id', id);
    formData.append('title', title);
    formData.append('solution', solution);
    formData.append('status', status);
    fetch(appBase + '/dashboard', {
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
    if (!confirm('Supprimer cette FAQ ?')) return;
    showLoader();
    const formData = new FormData();
    formData.append('action', 'faq_delete');
    formData.append('id', faqId);
    fetch(appBase + '/dashboard', {
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
  };

  // PDF
  window.showPdfModal = function (pdfId) {
    const pdf = pdfId ? pdfData.find(p => p.id === pdfId) : null;
    document.getElementById('pdf-id').value = pdfId || '';
    document.getElementById('pdf-title').value = pdf ? pdf.title : '';
    document.getElementById('pdf-description').value = pdf ? pdf.description : '';
    document.getElementById('pdf-file-url').value = pdf ? pdf.fileUrl : '';
    document.getElementById('pdf-keywords').value = pdf && Array.isArray(pdf.keywords) ? pdf.keywords.join(', ') : (pdf ? pdf.keywords : '');
    document.getElementById('modal-pdf-title').textContent = pdfId ? 'Modifier le PDF' : 'Ajouter un PDF';
    document.getElementById('modal-pdf').style.display = 'flex';
  };

  document.getElementById('form-pdf')?.addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('pdf-id').value;
    const title = document.getElementById('pdf-title').value.trim();
    const description = document.getElementById('pdf-description').value.trim();
    const fileUrl = document.getElementById('pdf-file-url').value.trim();
    const keywordsStr = document.getElementById('pdf-keywords').value.trim();
    const keywords = keywordsStr ? keywordsStr.split(',').map(k => k.trim()).filter(k => k) : [];
    if (!title) {
      showError('Veuillez saisir un titre');
      return;
    }
    showLoader();
    const formData = new FormData();
    formData.append('action', id ? 'pdf_update' : 'pdf_create');
    if (id) formData.append('id', id);
    formData.append('title', title);
    formData.append('description', description);
    formData.append('fileUrl', fileUrl);
    formData.append('keywords', JSON.stringify(keywords));
    fetch(appBase + '/dashboard', {
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
          showError('Erreur lors de l\'enregistrement');
        }
      })
      .catch(err => {
        hideLoader();
        showError('Erreur réseau');
      });
  });

  window.deletePdf = function (pdfId) {
    if (!confirm('Supprimer ce PDF ?')) return;
    showLoader();
    const formData = new FormData();
    formData.append('action', 'pdf_delete');
    formData.append('id', pdfId);
    fetch(appBase + '/dashboard', {
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
  };

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
