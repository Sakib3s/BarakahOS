document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('app-loaded');

    const skipButtons = document.querySelectorAll('.fixed-task-skip-button');
    const modalSourceType = document.getElementById('skip-modal-source-type');
    const modalSourceId = document.getElementById('skip-modal-source-id');
    const modalTaskTitle = document.getElementById('skip-modal-task-title');
    const modalSkipNote = document.getElementById('skip_note');
    const modalGeneralNote = document.getElementById('skip-modal-general-note');
    const skipNoteForm = document.getElementById('skip-note-form');

    skipButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const generalNoteInputId = button.dataset.generalNoteInputId;
            const generalNoteInput = generalNoteInputId ? document.getElementById(generalNoteInputId) : null;

            if (modalSourceType) modalSourceType.value = button.dataset.sourceType || '';
            if (modalSourceId) modalSourceId.value = button.dataset.sourceId || '';
            if (modalTaskTitle) modalTaskTitle.textContent = button.dataset.title || '';
            if (modalSkipNote) {
                modalSkipNote.value = button.dataset.skipNote || '';
                modalSkipNote.classList.remove('is-invalid');
            }
            if (modalGeneralNote) modalGeneralNote.value = generalNoteInput ? generalNoteInput.value : '';
        });
    });

    if (skipNoteForm && modalSkipNote) {
        skipNoteForm.addEventListener('submit', (event) => {
            if (modalSkipNote.value.trim() !== '') {
                modalSkipNote.classList.remove('is-invalid');

                return;
            }

            event.preventDefault();
            modalSkipNote.classList.add('is-invalid');
            modalSkipNote.focus();
        });
    }

    const timerElements = document.querySelectorAll('.focus-session-timer');

    timerElements.forEach((timerElement) => {
        const startTime = timerElement.dataset.startTime;

        if (!startTime) {
            return;
        }

        const startTimestamp = new Date(startTime.replace(' ', 'T'));

        const updateTimer = () => {
            const diffSeconds = Math.max(0, Math.floor((Date.now() - startTimestamp.getTime()) / 1000));
            const hours = String(Math.floor(diffSeconds / 3600)).padStart(2, '0');
            const minutes = String(Math.floor((diffSeconds % 3600) / 60)).padStart(2, '0');
            const seconds = String(diffSeconds % 60).padStart(2, '0');

            timerElement.textContent = `${hours}:${minutes}:${seconds}`;
        };

        updateTimer();
        window.setInterval(updateTimer, 1000);
    });
});
