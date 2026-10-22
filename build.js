// Copie les fichiers du site dans dist/ (aucune dépendance).
const fs = require('fs');
const path = require('path');

const SORTIE = path.join(__dirname, 'dist');
const A_COPIER = ['index.html', 'img'];

fs.rmSync(SORTIE, { recursive: true, force: true });
fs.mkdirSync(SORTIE, { recursive: true });
for (const nom of A_COPIER) {
  fs.cpSync(path.join(__dirname, nom), path.join(SORTIE, nom), { recursive: true });
}
console.log('Site copié dans dist/');
