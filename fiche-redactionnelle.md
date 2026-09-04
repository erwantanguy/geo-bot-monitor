# GEO Bot Monitor — Fiche rédactionnelle

> **Plugin WordPress** — Surveillance des visites de robots SEO et GEO/AI avec tableaux de bord, exports, comparaisons de périodes et maintenance automatique.

---

## Téléchargement

- **Version actuelle :** 1.1.2
- **Fichier ZIP :** [https://dl.ticoet.me/downloads/pluginsWP/geo-bot-monitor/geo-bot-monitor.zip](https://dl.ticoet.me/downloads/pluginsWP/geo-bot-monitor/geo-bot-monitor.zip)
- **Mise à jour :** automatique via le tableau de bord WordPress (plugin-update-checker)

---

## Qu’est-ce que GEO Bot Monitor ?

**GEO Bot Monitor** enregistre et analyse les visites des robots sur votre site WordPress.

Il distingue les moteurs de recherche classiques (Googlebot, Bingbot) des crawlers IA et GEO (ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended, etc.) pour vous donner une vision claire de qui explore votre contenu et quand.

---

## Pourquoi surveiller les robots ?

| Objectif | Pourquoi c’est important |
|---|---|
| **SEO** | Vérifier que Google et Bing explorent bien vos pages |
| **AEO / GEO** | Savoir si les IA indexent et citent votre contenu |
| **Performance** | Détecter les robots trop agressifs qui ralentissent le site |
| **Sécurité** | Identifier les crawlers indésirables ou malveillants |
| **Stratégie éditoriale** | Corréler l’activité des bots avec les publications |

---

## Fonctionnalités principales

### 1. Détection automatique des robots
Le plugin identifie les principaux crawlers par leur user-agent et leur catégorise :

- **Moteurs de recherche** : Googlebot, Bingbot, Yahoo, DuckDuckGo, etc.
- **IA / GEO** : ChatGPT-User, ClaudeBot, PerplexityBot, Google-Extended, etc.
- **SEO tools** : AhrefsBot, SemrushBot, Screaming Frog, etc.
- **Réseaux sociaux** : Facebook, Twitter, LinkedIn, etc.
- **Autres** : archive.org, RSS readers, etc.

### 2. Tableau de bord visuel
- Nombre total de visites
- Répartition par catégorie
- Évolution quotidienne sur la période choisie
- Top 20 des robots les plus actifs
- Graphiques interactifs (Chart.js)

### 3. Comparaison de périodes
Comparez l’activité des robots entre deux périodes pour mesurer l’impact d’une mise à jour, d’une campagne ou d’une publication.

### 4. Export des données
Exportez les logs au format **CSV** pour une analyse externe ou un reporting client.

### 5. Blocage de robots indésirables
Possibilité de bloquer certains user-agents via les réglages.

### 6. Maintenance automatique *(nouveau en v1.1.2)*
Pour éviter une explosion de la base de données :

- **Purge automatique** des logs anciens (durée configurable, par défaut 90 jours)
- **Optimisation** de la table après purge
- **Alerte admin** si la table dépasse un seuil défini (par défaut 100 Mo)
- **Nettoyage manuel rapide** depuis le tableau de bord
- **Compression des user-agents** trop longs

### 7. API sécurisée
Une clé API permet de récupérer les données de Bot Monitor depuis une application externe.

---

## Cas d’usage concret

Sur un site e-commerce ou local, GEO Bot Monitor permet de répondre à des questions comme :

- *ChatGPT explore-t-il mes pages produits ?*
- *Googlebot est-il passé depuis ma dernière mise à jour ?*
- *Un robot inconnu ralentit-il mon serveur ?*
- *Mes nouveaux articles sont-ils rapidement découverts ?*

---

## Compatibilité

- **WordPress :** 6.0+
- **PHP :** 7.4+
- **Stockage :** table MySQL `wp_geo_bot_visits` avec index optimisés

---

## Installation

1. Télécharger le ZIP : [geo-bot-monitor.zip](https://dl.ticoet.me/downloads/pluginsWP/geo-bot-monitor/geo-bot-monitor.zip)
2. Dans WordPress : **Extensions → Ajouter → Téléverser une extension**
3. Activer le plugin
4. Le suivi démarre automatiquement
5. Consulter **Bot Monitor → Tableau de bord**

---

## À qui s’adresse ce plugin ?

- Référenceurs et SEO
- Agences web et agences GEO
- Administrateurs de sites à fort trafic robot
- Sites sensibles à l’indexation IA
- Tout site voulant maîtriser son crawl budget

---

## Auteur

**Erwan Tanguy — Ticoët**
- Site : [https://www.ticoet.fr/](https://www.ticoet.fr/)
- Téléchargement : [https://dl.ticoet.me/downloads/pluginsWP/geo-bot-monitor/](https://dl.ticoet.me/downloads/pluginsWP/geo-bot-monitor/)
