// Petit serveur de fichiers statiques pour le mode « application Node.js » de Hostinger.
const http = require('http');
const fs = require('fs');
const path = require('path');

const RACINE = fs.existsSync(path.join(__dirname, 'dist')) ? path.join(__dirname, 'dist') : __dirname;
const TYPES = {
  '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript',
  '.png': 'image/png', '.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.svg': 'image/svg+xml',
  '.webp': 'image/webp', '.ico': 'image/x-icon', '.json': 'application/json', '.xml': 'application/xml',
  '.txt': 'text/plain; charset=utf-8'
};

http.createServer((req, res) => {
  let chemin = decodeURIComponent(new URL(req.url, 'http://x').pathname);
  if (chemin.endsWith('/')) chemin += 'index.html';
  const fichier = path.normalize(path.join(RACINE, chemin));
  if (!fichier.startsWith(RACINE)) { res.writeHead(403); return res.end(); }
  fs.readFile(fichier, (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' });
      return res.end('Page introuvable');
    }
    res.writeHead(200, { 'Content-Type': TYPES[path.extname(fichier)] || 'application/octet-stream' });
    res.end(data);
  });
}).listen(process.env.PORT || 3000);
