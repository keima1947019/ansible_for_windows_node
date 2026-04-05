git add .
git commit -m $(date +%Y-%m-%d_%H%M)
git pull origin main --allow-unrelated-histories
git push -u origin main
