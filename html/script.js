// eager anchors
window.addEventListener("mousedown", event => {
	if (event.button != 0) return;
	const anchor = event.target.closest("a");
	if (anchor == null) return;
	anchor.click();
});

window.addEventListener("keydown", event => {
	const target = event.target;

	let code = [];
	if (event.ctrlKey) code.push("Ctrl");
	if (event.altKey || event.metaKey) code.push("Alt");
	if (event.shiftKey) code.push("Shift");

	let key = event.key;
	if (key != "Control" && key != "Alt" && key != "Meta" && key != "Shift")
		code.push(key.charAt(0).toUpperCase() + key.slice(1));

	if (
		code.filter(piece => piece != "Shift" && piece.length > 1).length < 1 &&
		(target.tagName == "INPUT" || target.tagName == "TEXTAREA")
	) return;

	code = code.join("+");
	const el = document.querySelector(`[x-key-combo="${code}"]`);

	if (el != null) {
		el.click();
		event.stopPropagation();
	}
});

document.addEventListener("DOMContentLoaded", () => {
	document.querySelectorAll("[x-key-combo]").forEach(el => {
		el.classList.add("has-tooltip");
		const span = document.createElement("span");
		span.classList.add("tooltip");
		span.innerText = "Key combo: " + el.getAttribute("x-key-combo");
		el.appendChild(span);
	});
});
