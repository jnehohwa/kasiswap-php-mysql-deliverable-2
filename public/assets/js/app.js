document.addEventListener("DOMContentLoaded", () => {
  const navToggle = document.querySelector("[data-nav-toggle]");
  const nav = document.querySelector("[data-nav]");

  navToggle?.addEventListener("click", () => {
    nav?.classList.toggle("open");
  });

  document.querySelectorAll("[data-confirm]").forEach((button) => {
    button.addEventListener("click", (event) => {
      const message = button.getAttribute("data-confirm") || "Are you sure?";
      if (!window.confirm(message)) {
        event.preventDefault();
      }
    });
  });

  const syncStoreField = (select) => {
    const form = select.closest("form");
    const storeField = form?.querySelector("[data-store-field]");
    if (storeField) {
      storeField.hidden = select.value !== "seller";
    }
  };

  document.querySelectorAll("[data-role-select]").forEach((select) => {
    syncStoreField(select);
    select.addEventListener("change", () => syncStoreField(select));
  });
});
