import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['overlay', 'form', 'submitBtn', 'success', 'nom', 'telephone']

    connect() {
        if (sessionStorage.getItem('rappel_shown')) return

        this.timer = setTimeout(() => this.open(), 30000)

        document.addEventListener('mouseleave', this.handleExitIntent)
    }

    disconnect() {
        clearTimeout(this.timer)
        document.removeEventListener('mouseleave', this.handleExitIntent)
    }

    handleExitIntent = (e) => {
        if (e.clientY <= 0 && !sessionStorage.getItem('rappel_shown')) {
            this.open()
        }
    }

    open() {
        sessionStorage.setItem('rappel_shown', '1')
        this.overlayTarget.classList.add('open')
    }

    close() {
        this.overlayTarget.classList.remove('open')
    }

    async submit(e) {
        e.preventDefault()

        const nom = this.nomTarget.value.trim()
        const telephone = this.telephoneTarget.value.trim()

        if (!nom || !telephone) return

        this.submitBtnTarget.disabled = true
        this.submitBtnTarget.textContent = 'Envoi...'

        try {
            const response = await fetch('/rappel-gratuit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `nom=${encodeURIComponent(nom)}&telephone=${encodeURIComponent(telephone)}`,
            })

            if (response.ok) {
                this.formTarget.style.display = 'none'
                this.successTarget.style.display = 'block'
                setTimeout(() => this.close(), 3000)
            }
        } catch {
            this.submitBtnTarget.disabled = false
            this.submitBtnTarget.textContent = 'Me faire rappeler'
        }
    }
}
