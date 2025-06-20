/**
 * AI Quiz Generator JavaScript
 * Enhances user experience with interactive features
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeAIQuiz();
});

function initializeAIQuiz() {
    // Initialize quiz form enhancements
    initQuizFormEnhancements();

    // Initialize progress tracking
    initProgressTracking();

    // Initialize accessibility features
    initAccessibilityFeatures();
}



/**
 * Quiz Form Enhancements
 */
function initQuizFormEnhancements() {
    // Auto-scroll to next question when answered
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            const currentCard = this.closest('.question-card');
            const nextCard = currentCard ? currentCard.nextElementSibling : null;
            
            // Add visual feedback
            const label = this.closest('.option-label');
            if (label) {
                label.style.transform = 'scale(1.02)';
                setTimeout(() => {
                    label.style.transform = '';
                }, 200);
            }
            
            // Auto-scroll to next question (with delay)
            if (nextCard && nextCard.classList.contains('question-card')) {
                setTimeout(() => {
                    nextCard.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 300);
            }
        });
    });
    
    // Form validation enhancements
    const forms = document.querySelectorAll('form[data-request]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showValidationErrors();
            }
        });
    });
}

/**
 * Progress Tracking
 */
function initProgressTracking() {
    const questionCards = document.querySelectorAll('.question-card');
    
    if (questionCards.length === 0) return;
    
    // Create progress indicator
    createProgressIndicator(questionCards.length);
    
    // Track progress
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
    
    // Initial progress update
    updateProgress();
}

function createProgressIndicator(totalQuestions) {
    const existingIndicator = document.getElementById('quiz-progress-indicator');
    if (existingIndicator) return;
    
    const progressHTML = `
        <div id="quiz-progress-indicator" class="quiz-progress-indicator mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Progress</small>
                <small class="text-muted">
                    <span id="answered-count">0</span>/${totalQuestions} answered
                </small>
            </div>
            <div class="progress mt-1" style="height: 6px;">
                <div id="progress-bar" class="progress-bar bg-primary" role="progressbar" 
                     style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
        </div>
    `;
    
    const quizContainer = document.querySelector('.ai-generated-quiz .card-body');
    if (quizContainer) {
        quizContainer.insertAdjacentHTML('afterbegin', progressHTML);
    }
}

function updateProgress() {
    const totalQuestions = document.querySelectorAll('.question-card').length;
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;
    const percentage = totalQuestions > 0 ? (answeredQuestions / totalQuestions) * 100 : 0;
    
    const progressBar = document.getElementById('progress-bar');
    const answeredCount = document.getElementById('answered-count');
    
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
    }
    
    if (answeredCount) {
        answeredCount.textContent = answeredQuestions;
    }
    
    // Enable submit button when all questions are answered
    const submitButton = document.querySelector('button[type="submit"]');
    if (submitButton) {
        if (answeredQuestions === totalQuestions) {
            submitButton.classList.remove('btn-outline-primary');
            submitButton.classList.add('btn-primary');
            submitButton.innerHTML = '<i class="bi bi-check-lg me-2"></i>Submit Complete Quiz';
        } else {
            submitButton.classList.add('btn-outline-primary');
            submitButton.classList.remove('btn-primary');
            submitButton.innerHTML = '<i class="bi bi-check-lg me-2"></i>Submit Quiz & Get AI Explanations';
        }
    }
}

/**
 * Accessibility Features
 */
function initAccessibilityFeatures() {
    // Keyboard navigation for option labels
    const optionLabels = document.querySelectorAll('.option-label');
    
    optionLabels.forEach(label => {
        label.setAttribute('tabindex', '0');
        
        label.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            }
        });
    });
    
    // Announce progress to screen readers
    const progressAnnouncer = document.createElement('div');
    progressAnnouncer.setAttribute('aria-live', 'polite');
    progressAnnouncer.setAttribute('aria-atomic', 'true');
    progressAnnouncer.className = 'sr-only';
    progressAnnouncer.id = 'progress-announcer';
    document.body.appendChild(progressAnnouncer);
}

/**
 * Utility Functions
 */


function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (field.type === 'radio') {
            const radioGroup = form.querySelectorAll(`input[name="${field.name}"]`);
            const isChecked = Array.from(radioGroup).some(radio => radio.checked);
            if (!isChecked) {
                isValid = false;
                highlightError(field.closest('.question-card'));
            }
        } else if (!field.value.trim()) {
            isValid = false;
            highlightError(field);
        }
    });
    
    return isValid;
}

function highlightError(element) {
    element.classList.add('border-danger');
    setTimeout(() => {
        element.classList.remove('border-danger');
    }, 3000);
}

function showValidationErrors() {
    const errorMessage = document.createElement('div');
    errorMessage.className = 'alert alert-warning alert-dismissible fade show';
    errorMessage.innerHTML = `
        <i class="bi bi-exclamation-triangle me-2"></i>
        Please answer all questions before submitting.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.ai-generated-quiz .card-body');
    if (container) {
        container.insertBefore(errorMessage, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorMessage.parentNode) {
                errorMessage.remove();
            }
        }, 5000);
    }
}

// Export functions for external use
window.AIQuiz = {
    updateProgress,
    validateForm
};
