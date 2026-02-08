/**
 * Système de modales — remplace alert() et confirm()
 */

(function () {
  'use strict';

  // Créer le conteneur de modales s'il n'existe pas
  let modalContainer = document.getElementById('app-modal-container');
  if (!modalContainer) {
    modalContainer = document.createElement('div');
    modalContainer.id = 'app-modal-container';
    document.body.appendChild(modalContainer);
  }

  /**
   * Affiche une modale de confirmation
   */
  window.showConfirmModal = function (message, onConfirm, onCancel) {
    const modalId = 'confirm-modal-' + Date.now();
    const modalHtml = `
      <div id="${modalId}" class="modal-back" style="display: flex;">
        <div class="modal modal--confirm">
          <div class="modal__header">
            <h2>Confirmation</h2>
            <button onclick="closeAppModal('${modalId}')" class="btn btn--ghost">✕</button>
          </div>
          <div class="modal__body">
            <p>${escapeHtml(message)}</p>
          </div>
          <div class="modal__actions">
            <button type="button" class="btn btn--ghost modal-cancel-btn">Annuler</button>
            <button type="button" class="btn btn--primary modal-confirm-btn">Confirmer</button>
          </div>
        </div>
      </div>
    `;
    modalContainer.insertAdjacentHTML('beforeend', modalHtml);
    
    const modal = document.getElementById(modalId);
    const confirmBtn = modal.querySelector('.btn--primary');
    
    confirmBtn.onclick = function () {
      closeAppModal(modalId);
      if (onConfirm) {
        setTimeout(function () {
          if (typeof onConfirm === 'function') {
            onConfirm();
          }
        }, 100);
      }
    };
    
    const cancelBtn = modal.querySelector('.btn--ghost');
    cancelBtn.onclick = function () {
      closeAppModal(modalId);
      if (onCancel) {
        setTimeout(function () {
          if (typeof onCancel === 'function') {
            onCancel();
          }
        }, 100);
      }
    };
  };

  /**
   * Affiche une modale d'alerte
   */
  window.showAlertModal = function (message, type = 'info') {
    const modalId = 'alert-modal-' + Date.now();
    const iconClass = type === 'error' ? 'fa-exclamation-circle' : (type === 'success' ? 'fa-check-circle' : 'fa-info-circle');
    const modalHtml = `
      <div id="${modalId}" class="modal-back" style="display: flex;">
        <div class="modal modal--alert">
          <div class="modal__header">
            <h2>
              <i class="fas ${iconClass}"></i>
              <span>${type === 'error' ? 'Erreur' : (type === 'success' ? 'Succès' : 'Information')}</span>
            </h2>
            <button onclick="closeAppModal('${modalId}')" class="btn btn--ghost">✕</button>
          </div>
          <div class="modal__body">
            <p>${escapeHtml(message)}</p>
          </div>
          <div class="modal__actions">
            <button type="button" onclick="closeAppModal('${modalId}')" class="btn btn--primary">OK</button>
          </div>
        </div>
      </div>
    `;
    modalContainer.insertAdjacentHTML('beforeend', modalHtml);
  };

  /**
   * Ferme une modale
   */
  window.closeAppModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = 'none';
      setTimeout(function () {
        modal.remove();
      }, 200);
    }
  };

  /**
   * Fonction d'échappement HTML
   */
  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
})();
