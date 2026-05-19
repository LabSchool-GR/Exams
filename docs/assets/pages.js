(function () {
    const storageKey = "labschool-exams-docs-language";
    const common = {
        en: {
            brand: "LabSchool Exams",
            navHome: "Home",
            navHomeShort: "Home",
            navLearn: "User Guide",
            navLearnShort: "Guide",
            navSponsor: "Support",
            navSponsorShort: "Support",
            backToTop: "Back to top",
            returnHome: "Home Page",
            returnHomeShort: "Back",
            footer: "LabSchool Exams is an open-source knowledge assessment application built with Laravel."
        },
        el: {
            brand: "LabSchool Exams",
            navHome: "Αρχική Σελίδα",
            navHomeShort: "Αρχική",
            navLearn: "Οδηγός Χρήσης",
            navLearnShort: "Οδηγός",
            navSponsor: "Υποστήριξη Έργου",
            navSponsorShort: "Υποστήριξη",
            backToTop: "Επιστροφή πάνω",
            returnHome: "Αρχική Σελίδα",
            returnHomeShort: "Επιστροφή",
            footer: "Το LabSchool Exams είναι εφαρμογή αξιολόγησης γνώσεων ανοιχτού κώδικα βασισμένη στο Laravel."
        }
    };

    const navigation = {
        home: "index.html",
        learn: "learn.html",
        sponsor: "sponsor.html"
    };

    function renderLayout() {
        const header = document.querySelector("[data-page-header]");
        const footer = document.querySelector("[data-page-footer]");

        if (header) {
            header.innerHTML = `
                <a class="brand" href="index.html" aria-label="LabSchool Exams">
                    <span class="brand-mark">EX</span>
                    <span data-common-i18n="brand">LabSchool Exams</span>
                </a>
                <nav class="page-nav" aria-label="Primary">
                    <a class="nav-link" href="${navigation.home}">
                        <span class="nav-label-full" data-common-i18n="navHome">Home</span>
                        <span class="nav-label-short" data-common-i18n="navHomeShort">Home</span>
                    </a>
                    <a class="nav-link" href="${navigation.learn}">
                        <span class="nav-label-full" data-common-i18n="navLearn">User Guide</span>
                        <span class="nav-label-short" data-common-i18n="navLearnShort">Guide</span>
                    </a>
                    <a class="nav-link" href="${navigation.sponsor}">
                        <span class="nav-label-full" data-common-i18n="navSponsor">Support</span>
                        <span class="nav-label-short" data-common-i18n="navSponsorShort">Support</span>
                    </a>
                    <div class="language-switch" aria-label="Language">
                        <button type="button" data-language-option="en" aria-pressed="true">EN</button>
                        <button type="button" data-language-option="el" aria-pressed="false">EL</button>
                    </div>
                </nav>
            `;
        }

        if (footer) {
            footer.classList.add("footer");
            footer.innerHTML = `
                <span data-common-i18n="footer"></span>
                <a class="footer-home-link" href="${navigation.home}">
                    <span class="nav-label-full" data-common-i18n="returnHome">Back to Home Page</span>
                    <span class="nav-label-short" data-common-i18n="returnHomeShort">Back</span>
                </a>
            `;
        }

        if (!document.querySelector("[data-back-to-top]")) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "back-to-top";
            button.setAttribute("data-back-to-top", "");
            button.setAttribute("data-common-i18n-aria", "backToTop");
            button.innerHTML = '<span aria-hidden="true">↑</span>';
            document.body.appendChild(button);
        }
    }

    function detectLanguage() {
        const saved = localStorage.getItem(storageKey);
        if (saved === "en" || saved === "el") {
            return saved;
        }

        return navigator.language && navigator.language.toLowerCase().startsWith("el") ? "el" : "en";
    }

    function translate(language) {
        const page = window.ExamsPage || {};
        const pageTranslations = page.translations || {};
        const dictionary = pageTranslations[language] || pageTranslations.en || {};
        const commonDictionary = common[language] || common.en;

        document.documentElement.lang = language;
        localStorage.setItem(storageKey, language);

        if (dictionary.pageTitle) {
            document.title = dictionary.pageTitle;
        }

        if (dictionary.description) {
            const description = document.querySelector('meta[name="description"]');
            if (description) {
                description.setAttribute("content", dictionary.description);
            }
        }

        document.querySelectorAll("[data-common-i18n]").forEach((element) => {
            element.textContent = commonDictionary[element.dataset.commonI18n] || "";
        });

        document.querySelectorAll("[data-common-i18n-aria]").forEach((element) => {
            element.setAttribute("aria-label", commonDictionary[element.dataset.commonI18nAria] || "");
            element.setAttribute("title", commonDictionary[element.dataset.commonI18nAria] || "");
        });

        document.querySelectorAll("[data-i18n]").forEach((element) => {
            element.textContent = dictionary[element.dataset.i18n] || "";
        });

        document.querySelectorAll("[data-i18n-aria]").forEach((element) => {
            element.setAttribute("aria-label", dictionary[element.dataset.i18nAria] || "");
        });

        document.querySelectorAll("[data-doc-link]").forEach((element) => {
            const key = element.dataset.docLink;
            const links = page.links || {};
            const href = links[language]?.[key] || links.en?.[key];
            if (href) {
                element.href = href;
            }
        });

        document.querySelectorAll("[data-language-option]").forEach((button) => {
            button.setAttribute("aria-pressed", button.dataset.languageOption === language ? "true" : "false");
        });
    }

    function setupBackToTop() {
        const button = document.querySelector("[data-back-to-top]");
        if (!button) {
            return;
        }

        const updateVisibility = () => {
            const pageHasOverflow = document.documentElement.scrollHeight > window.innerHeight + 80;
            const hasScrolled = window.scrollY > Math.min(360, window.innerHeight / 2);
            button.classList.toggle("is-visible", pageHasOverflow && hasScrolled);
        };

        button.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });

        window.addEventListener("scroll", updateVisibility, { passive: true });
        window.addEventListener("resize", updateVisibility);
        updateVisibility();
    }

    document.addEventListener("DOMContentLoaded", () => {
        renderLayout();
        setupBackToTop();

        document.querySelectorAll("[data-language-option]").forEach((button) => {
            button.addEventListener("click", () => translate(button.dataset.languageOption));
        });

        translate(detectLanguage());
    });
})();
