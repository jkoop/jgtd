// eager anchors
window.addEventListener("mousedown", event => {
    if (event.button != 0) return;
    const anchor = event.target.closest("a");
    if (anchor == null) return;
    anchor.click();
});
