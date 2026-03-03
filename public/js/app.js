// Gestion des onglets Vulnérable / Sécurisé
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var group = this.closest('.card');
            group.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
            group.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
            this.classList.add('active');
            var target = group.querySelector('#' + this.dataset.tab);
            if (target) target.classList.add('active');
        });
    });
});
