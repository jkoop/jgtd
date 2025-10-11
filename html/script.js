// eager anchors
window.addEventListener("mousedown", event => {
    const anchor = event.target.closest("a");
    if (anchor == null) return;
    location.href = anchor.href;
});
