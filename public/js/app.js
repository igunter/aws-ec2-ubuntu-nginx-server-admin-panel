/**
 * Wire a name input to auto-populate a slug input.
 * Auto-fill stops as soon as the user manually edits the slug field.
 *
 * @param {string} nameId  - id of the name <input>
 * @param {string} slugId  - id of the slug <input>
 */
function bindSlugAutofill(nameId, slugId) {
    var name = document.getElementById(nameId);
    var slug = document.getElementById(slugId);
    if (!name || !slug) return;

    var dirty = slug.value !== '';

    slug.addEventListener('input', function () {
        dirty = slug.value !== '';
    });

    name.addEventListener('input', function () {
        if (dirty) return;
        slug.value = name.value
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    });
}
