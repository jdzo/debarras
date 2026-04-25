import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['step', 'progress', 'progressFill', 'stepLabel', 'prevBtn', 'nextBtn', 'submitBtn', 'apercu']
    static values = { current: { type: Number, default: 0 }, total: Number }

    static stepTitles = [
        'Type de bien',
        'Superficie',
        'Encombrement',
        'Saleté',
        'Accessibilité',
        'Options',
        'Détails',
        'Coordonnées',
    ]

    connect() {
        this.totalValue = this.stepTargets.length
        this.showStep()
    }

    next() {
        if (!this.validateCurrentStep()) return
        if (this.currentValue < this.totalValue - 1) {
            this.currentValue++
            this.showStep()
        }
    }

    prev() {
        if (this.currentValue > 0) {
            this.currentValue--
            this.showStep()
        }
    }

    showStep() {
        this.stepTargets.forEach((step, index) => {
            step.style.display = index === this.currentValue ? 'block' : 'none'
        })

        const progress = ((this.currentValue + 1) / this.totalValue) * 100
        if (this.hasProgressFillTarget) {
            this.progressFillTarget.style.width = `${progress}%`
        }
        if (this.hasStepLabelTarget) {
            const title = this.constructor.stepTitles[this.currentValue] || ''
            this.stepLabelTarget.textContent = `Étape ${this.currentValue + 1} / ${this.totalValue} — ${title}`
        }

        if (this.hasPrevBtnTarget) {
            this.prevBtnTarget.style.display = this.currentValue === 0 ? 'none' : 'inline-block'
        }
        if (this.hasNextBtnTarget) {
            this.nextBtnTarget.style.display = this.currentValue === this.totalValue - 1 ? 'none' : 'inline-block'
        }
        if (this.hasSubmitBtnTarget) {
            this.submitBtnTarget.style.display = this.currentValue === this.totalValue - 1 ? 'inline-block' : 'none'
        }

        // Update price preview after options step
        if (this.currentValue >= 6 && this.hasApercuTarget) {
            this.updateApercu()
        }
    }

    selectOption(event) {
        const container = event.target.closest('[data-group]')
        if (!container) return

        container.querySelectorAll('.option-card').forEach(card => {
            card.classList.remove('selected')
        })

        const card = event.target.closest('.option-card')
        if (card) {
            card.classList.add('selected')
            const radio = card.querySelector('input[type="radio"]')
            if (radio) radio.checked = true
        }
    }

    validateCurrentStep() {
        const currentStep = this.stepTargets[this.currentValue]
        const radios = currentStep.querySelectorAll('input[type="radio"]')

        if (radios.length > 0) {
            const name = radios[0].name
            const checked = currentStep.querySelector(`input[name="${name}"]:checked`)
            if (!checked) {
                currentStep.classList.add('shake')
                setTimeout(() => currentStep.classList.remove('shake'), 500)
                return false
            }
        }

        const requiredInputs = currentStep.querySelectorAll('input[required], select[required]')
        for (const input of requiredInputs) {
            if (!input.value.trim()) {
                input.focus()
                return false
            }
        }

        return true
    }

    async updateApercu() {
        const form = this.element
        const formData = new FormData(form)

        try {
            const response = await fetch('/estimation/apercu', {
                method: 'POST',
                body: formData,
            })

            if (response.ok) {
                const data = await response.json()
                if (this.hasApercuTarget) {
                    this.apercuTarget.textContent = data.formatte
                    this.apercuTarget.style.display = 'block'
                }
            }
        } catch (e) {
            // Silently fail — preview is optional
        }
    }
}
