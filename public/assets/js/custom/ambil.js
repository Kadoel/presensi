function updateDateTime() {
    const now = new Date();
    const optionsDate = {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    document.getElementById('date').innerHTML =
        now.toLocaleDateString('id-ID', optionsDate);

    document.getElementById('time').innerHTML =
        now.toLocaleTimeString('id-ID');
}
setInterval(updateDateTime, 1000);
updateDateTime();

const gradients = [
    "linear-gradient(135deg, #667eea, #764ba2)",
    "linear-gradient(135deg, #11998e, #38ef7d)",
    "linear-gradient(135deg, #fc466b, #3f5efb)",
    "linear-gradient(135deg, #f7971e, #ffd200)",
    "linear-gradient(135deg, #00c6ff, #0072ff)",
    "linear-gradient(135deg, #ff512f, #dd2476)"
];

const buttons = document.querySelectorAll(".queue-btn");

buttons.forEach((btn, index) => {
    btn.style.background = gradients[index % gradients.length];
});

document.querySelectorAll(".queue-btn").forEach(button => {
    button.addEventListener("click", function (e) {
        const ripple = document.createElement("span");
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        ripple.style.width = ripple.style.height = size + "px";
        ripple.style.left = e.clientX - rect.left - size / 2 + "px";
        ripple.style.top = e.clientY - rect.top - size / 2 + "px";
        ripple.classList.add("ripple");
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
    });
});