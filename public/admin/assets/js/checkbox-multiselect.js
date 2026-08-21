/**
 * Checkbox Multi-Select Component
 * A custom multi-select with checkboxes and search functionality
 */

class CheckboxMultiSelect {
    constructor(element, options = {}) {
        this.originalSelect = element;
        this.options = {
            placeholder: options.placeholder || 'Select options...',
            searchPlaceholder: options.searchPlaceholder || 'Search...',
            type: options.type || 'default',
            showTags: options.showTags !== false,
            maxTagsVisible: options.maxTagsVisible || 5,
            ...options
        };

        this.isOpen = false;
        this.selectedValues = [];

        this.init();
    }

    init() {
        // Hide original select
        this.originalSelect.style.display = 'none';

        // Get initial selected values
        this.selectedValues = Array.from(this.originalSelect.selectedOptions).map(opt => opt.value);

        // Build the component
        this.buildComponent();
        this.bindEvents();
        this.updateDisplay();
    }

    buildComponent() {
        // Create wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'checkbox-multiselect';
        this.wrapper.setAttribute('data-type', this.options.type);

        // Create toggle button
        this.toggle = document.createElement('div');
        this.toggle.className = 'checkbox-multiselect-toggle';
        this.toggle.innerHTML = `
            <span class="selected-text placeholder">${this.options.placeholder}</span>
            <span class="toggle-icon">
                <i class="feather-chevron-down"></i>
            </span>
        `;

        // Create dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'checkbox-multiselect-dropdown';

        // Search box
        const searchBox = document.createElement('div');
        searchBox.className = 'checkbox-multiselect-search';
        searchBox.innerHTML = `<input type="text" placeholder="${this.options.searchPlaceholder}">`;
        this.searchInput = searchBox.querySelector('input');

        // Actions bar
        const actionsBar = document.createElement('div');
        actionsBar.className = 'checkbox-multiselect-actions';
        actionsBar.innerHTML = `
            <div>
                <button type="button" class="select-all-btn">Select All</button>
                <button type="button" class="clear-all-btn">Clear All</button>
            </div>
            <span class="result-count"></span>
        `;

        // Options container
        this.optionsContainer = document.createElement('div');
        this.optionsContainer.className = 'checkbox-multiselect-options';

        // Build options from original select
        Array.from(this.originalSelect.options).forEach(option => {
            if (option.value === '') return; // Skip empty option

            const optionEl = document.createElement('div');
            optionEl.className = 'checkbox-option';
            optionEl.setAttribute('data-value', option.value);
            optionEl.setAttribute('data-search', option.textContent.toLowerCase());

            if (this.selectedValues.includes(option.value)) {
                optionEl.classList.add('selected');
            }

            optionEl.innerHTML = `
                <input type="checkbox" ${this.selectedValues.includes(option.value) ? 'checked' : ''}>
                <span class="checkbox-custom"></span>
                <div class="option-label">
                    <div class="option-title">${option.textContent}</div>
                </div>
            `;

            this.optionsContainer.appendChild(optionEl);
        });

        // Assemble dropdown
        this.dropdown.appendChild(searchBox);
        this.dropdown.appendChild(actionsBar);
        this.dropdown.appendChild(this.optionsContainer);

        // Selected tags container
        if (this.options.showTags) {
            this.tagsContainer = document.createElement('div');
            this.tagsContainer.className = 'selected-tags-container';
            this.wrapper.appendChild(this.tagsContainer);
        }

        // Assemble component
        this.wrapper.appendChild(this.toggle);
        this.wrapper.appendChild(this.dropdown);

        // Insert after original select
        this.originalSelect.parentNode.insertBefore(this.wrapper, this.originalSelect.nextSibling);

        // Store references
        this.selectAllBtn = actionsBar.querySelector('.select-all-btn');
        this.clearAllBtn = actionsBar.querySelector('.clear-all-btn');
        this.resultCount = actionsBar.querySelector('.result-count');

        this.updateResultCount();
    }

    bindEvents() {
        // Toggle dropdown
        this.toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        // Option click
        this.optionsContainer.addEventListener('click', (e) => {
            const optionEl = e.target.closest('.checkbox-option');
            if (optionEl) {
                this.toggleOption(optionEl);
            }
        });

        // Search
        this.searchInput.addEventListener('input', () => {
            this.filterOptions(this.searchInput.value);
        });

        // Select all
        this.selectAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.selectAllVisible();
        });

        // Clear all
        this.clearAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.clearAll();
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!this.wrapper.contains(e.target)) {
                this.closeDropdown();
            }
        });

        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeDropdown();
            }
        });
    }

    toggleDropdown() {
        if (this.isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }

    openDropdown() {
        this.isOpen = true;
        this.wrapper.classList.add('open');
        this.searchInput.focus();
    }

    closeDropdown() {
        this.isOpen = false;
        this.wrapper.classList.remove('open');
        this.searchInput.value = '';
        this.filterOptions('');
    }

    toggleOption(optionEl) {
        const value = optionEl.getAttribute('data-value');
        const checkbox = optionEl.querySelector('input[type="checkbox"]');

        if (optionEl.classList.contains('selected')) {
            optionEl.classList.remove('selected');
            checkbox.checked = false;
            this.selectedValues = this.selectedValues.filter(v => v !== value);
        } else {
            optionEl.classList.add('selected');
            checkbox.checked = true;
            this.selectedValues.push(value);
        }

        this.syncOriginalSelect();
        this.updateDisplay();
    }

    filterOptions(query) {
        const searchTerm = query.toLowerCase().trim();
        const options = this.optionsContainer.querySelectorAll('.checkbox-option');
        let visibleCount = 0;

        options.forEach(opt => {
            const searchText = opt.getAttribute('data-search');
            if (searchTerm === '' || searchText.includes(searchTerm)) {
                opt.classList.remove('hidden');
                visibleCount++;
            } else {
                opt.classList.add('hidden');
            }
        });

        this.updateResultCount(visibleCount);
    }

    selectAllVisible() {
        const visibleOptions = this.optionsContainer.querySelectorAll('.checkbox-option:not(.hidden)');
        visibleOptions.forEach(opt => {
            if (!opt.classList.contains('selected')) {
                const value = opt.getAttribute('data-value');
                opt.classList.add('selected');
                opt.querySelector('input[type="checkbox"]').checked = true;
                if (!this.selectedValues.includes(value)) {
                    this.selectedValues.push(value);
                }
            }
        });

        this.syncOriginalSelect();
        this.updateDisplay();
    }

    clearAll() {
        const allOptions = this.optionsContainer.querySelectorAll('.checkbox-option');
        allOptions.forEach(opt => {
            opt.classList.remove('selected');
            opt.querySelector('input[type="checkbox"]').checked = false;
        });

        this.selectedValues = [];
        this.syncOriginalSelect();
        this.updateDisplay();
    }

    syncOriginalSelect() {
        Array.from(this.originalSelect.options).forEach(option => {
            option.selected = this.selectedValues.includes(option.value);
        });

        // Trigger change event
        const event = new Event('change', { bubbles: true });
        this.originalSelect.dispatchEvent(event);
    }

    updateDisplay() {
        const count = this.selectedValues.length;
        const textEl = this.toggle.querySelector('.selected-text');
        const countBadge = this.toggle.querySelector('.selected-count');

        if (count === 0) {
            textEl.textContent = this.options.placeholder;
            textEl.classList.add('placeholder');
            if (countBadge) countBadge.remove();
        } else {
            textEl.textContent = `${count} selected`;
            textEl.classList.remove('placeholder');

            if (!countBadge) {
                const badge = document.createElement('span');
                badge.className = 'selected-count';
                badge.textContent = count;
                textEl.after(badge);
            } else {
                countBadge.textContent = count;
            }
        }

        // Update tags
        if (this.options.showTags && this.tagsContainer) {
            this.updateTags();
        }
    }

    updateTags() {
        this.tagsContainer.innerHTML = '';

        const maxTags = this.options.maxTagsVisible;
        const displayValues = this.selectedValues.slice(0, maxTags);
        const remainingCount = this.selectedValues.length - maxTags;

        displayValues.forEach(value => {
            const option = this.originalSelect.querySelector(`option[value="${value}"]`);
            if (option) {
                const tag = document.createElement('span');
                tag.className = 'selected-tag';
                tag.innerHTML = `
                    <span class="tag-text">${this.truncateText(option.textContent, 25)}</span>
                    <span class="remove-tag" data-value="${value}">&times;</span>
                `;

                tag.querySelector('.remove-tag').addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeValue(value);
                });

                this.tagsContainer.appendChild(tag);
            }
        });

        if (remainingCount > 0) {
            const moreTag = document.createElement('span');
            moreTag.className = 'selected-tag';
            moreTag.style.background = '#6b7280';
            moreTag.innerHTML = `<span class="tag-text">+${remainingCount} more</span>`;
            this.tagsContainer.appendChild(moreTag);
        }
    }

    removeValue(value) {
        const optionEl = this.optionsContainer.querySelector(`[data-value="${value}"]`);
        if (optionEl) {
            optionEl.classList.remove('selected');
            optionEl.querySelector('input[type="checkbox"]').checked = false;
        }

        this.selectedValues = this.selectedValues.filter(v => v !== value);
        this.syncOriginalSelect();
        this.updateDisplay();
    }

    updateResultCount(count) {
        const total = this.optionsContainer.querySelectorAll('.checkbox-option').length;
        const visible = count !== undefined ? count : total;
        this.resultCount.textContent = `${visible} of ${total}`;
    }

    truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Public methods
    setDisabled(disabled) {
        if (disabled) {
            this.wrapper.classList.add('disabled');
            this.closeDropdown();
        } else {
            this.wrapper.classList.remove('disabled');
        }
    }

    getSelectedValues() {
        return [...this.selectedValues];
    }

    setSelectedValues(values) {
        this.selectedValues = values;

        const allOptions = this.optionsContainer.querySelectorAll('.checkbox-option');
        allOptions.forEach(opt => {
            const value = opt.getAttribute('data-value');
            if (values.includes(value)) {
                opt.classList.add('selected');
                opt.querySelector('input[type="checkbox"]').checked = true;
            } else {
                opt.classList.remove('selected');
                opt.querySelector('input[type="checkbox"]').checked = false;
            }
        });

        this.syncOriginalSelect();
        this.updateDisplay();
    }
}

// jQuery plugin wrapper
if (typeof jQuery !== 'undefined') {
    jQuery.fn.checkboxMultiSelect = function(options) {
        return this.each(function() {
            if (!this._checkboxMultiSelect) {
                this._checkboxMultiSelect = new CheckboxMultiSelect(this, options);
            }
        });
    };
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CheckboxMultiSelect;
}

window.CheckboxMultiSelect = CheckboxMultiSelect;
