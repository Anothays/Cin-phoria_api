#!/bin/bash

# Supprimer la base de données
if ! symfony console d:d:d -fq; then
  echo "Erreur lors de la suppression de la base de données."
  exit 1
fi

# Créer la base de données
if ! symfony console d:d:c -q; then
  echo "Erreur lors de la création de la base de données."
  exit 1
fi

# Créer le schéma
if ! symfony console d:s:c -q; then
  echo "Erreur lors de la création du schéma de la base de données."
  exit 1
fi

# Charger les fixtures si l'option --datafixtures est passée
if [[ "$1" == "--datafixtures" ]]; then
  if ! symfony console d:f:l -nq; then
    echo "Erreur lors du chargement des fixtures."
    exit 1
  fi
  echo "Fixtures loaded successfully."
fi

exit 0