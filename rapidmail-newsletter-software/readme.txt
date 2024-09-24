=== Wordpress Newsletter Plugin von rapidmail ===
Contributors: rapidmail GmbH
Tags: rapidmail, newsletter, newsletter tool, newsletter software, email marketing, email marketing service provider
Requires at least: 4.6
Tested up to: 5.5.1
Stable tag: 2.1.5
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Kostenloses Plugin zur Integration eines rapidmail Newsletter-Anmeldeformulars in Ihre Wordpress-Installation, mit Anmeldeformular in den Kommentaren.

== Description ==

Mit <a href="https://www.rapidmail.de/wordpress-plugin/lp?pid=o-wp&tid2=wporg">rapidmail</a> begeistern Sie Ihre Kontakte durch eindrucksvolle Newsletter. Einfaches und schnelles Erstellen dank intuitivem Editor und einer Vielzahl beeindruckender Vorlagen. Zuverlässiger Versand und Zustellung am Spamfilter vorbei – direkt ins Postfach des Empfängers.

Mit dem Wordpress Newsletter Plugin können Sie das Anmeldeformular für Ihren Newsletter ganz einfach auf Ihrer Wordpress-Website integrieren und nach Ihren Wünschen platzieren. Sie entscheiden dabei selbst, ob Sie auch die Abonnentengewinnung über Kommentare zulassen wollen oder nicht. Die Überschrift des Plugins können Sie nach Ihren Wünschen gestalten.
Bitte beachten Sie, dass für die Nutzung des Plugins ist ein aktiver rapidmail-Account erforderlich ist. <a href="https://www.rapidmail.de/wordpress-plugin/lp?pid=o-wp&tid2=wporg">Jetzt kostenlos bei rapidmail anmelden!</a>

= Die Funktionen des rapidmail Wordpress Newsletter Plugins: =
* Double-Opt-In Verfahren
* Eigene Überschrift für das Anmeldeformular
* Auswahl bestehender Empfängerlisten in denen Newsletter-Abonnenten gespeichert werden sollen
* Einfaches Einfügen des Anmeldeformulars per Drag and Drop-Funktion
* Alternatives Einfügen des Anmeldeformulars mit Wordpress-Shortcodes
* Abonnentengewinnung per Kommentar-Funktion
* DSGVO-konform & 100% made in germany

== Installation ==

1.  Laden Sie das rapidmail Wordpress Plugin in der Wordpress Plugin Directory (Plugins => Installieren => Plugin hochladen) hoch.
2.  Aktivieren Sie das rapidmail Wordpress-Plugin im Plugins-Menü
3.  Konfigurieren Sie das Plugin (Einstellungen => rapidmail)

Weitere Informationen erhalten Sie in der <a href="https://de.rapidmail.wiki/faq/wordpress-plugin/">rapidmail Hilfe-Datenbank</a>

== Frequently Asked Questions ==

= Ist das Newsletter-Anmeldeformular von rapidmail rechtssicher? =

Die Anmeldung zum Newsletter funktioniert nach dem Double-Opt-In Verfahren – das Abonnement eines Newsletters kommt also erst zustande, wenn der Empfänger nach dem Absenden des Anmeldeformulars den Link anklickt, welchen wir ihm in einer Anmelde-Bestätigungs-E-Mail gesendet haben. Wichtig: Erst nach der Verifizierung des Empfängers durch Anklicken des Bestätigungslinks wird er in die Verteilerliste aufgenommen.

= Kann ich bei rapidmail Newsletter mit Anhang versenden? =

Ja, mit rapidmail können Sie Ihren Newslettern ganz einfach einen Datei-Anhang hinzufügen. Ob Produktkatalog, Infobroschüre, Firmenportrait oder Werbeaktionen – Ihren Ideen sind dabei keine Grenzen gesetzt!

= Sind rapidmail Newsletter für mobile Geräte optimiert? =

Ja, rapidmail Newsletter sind für alle Arten von Endgeräten optimiert: Mit zahlreichen Vorlagen im Responsive Design erstellen Sie in wenigen Minuten mobil optimierte Newsletter, die auf allen Endgeräten gut ankommen!

= Kann ich individuelle und personalisierte Newsletter mit rapidmail versenden? =

Mit rapidmail und dem rapidmail Wordpress Newsletter-Plugin geben Sie Ihren Mailings ganz einfach eine persönliche Note. Daten Ihrer Empfänger, wie Name und Geschlecht, werden automatisch an Ihre rapidmail Empfängerliste übergeben – sie müssen diese nur mit einem Platzhalter in Ihr Mailing einfügen.

= Kommen meine Newsletter bei meinen Empfängern an? =

Die Zustellbarkeit Ihrer Newsletter ist eine der wichtigsten Faktoren Ihrer E-Mail Marketing Kampagnen. Damit Ihre Mailings auch wirklich ankommen, sind wir Mitglied der Certified Senders Allince (CSA). Somit sind wir auf der Whitelist für präferierte Zustellung bei allen großen deutschen Mail-Anbietern (gmx, web.de, yahoo, uvm.) und von der CSA für einen zuverlässigen und sicheren Versand von Newslettern zertifiziert.

= Was ist wenn ich Fragen habe? =

Unsere freundlichen Supportmitarbeiter helfen Ihnen gerne telefonisch, per E-Mail oder Kontaktformular. 100 % kostenlos.
Übrigens: Auf die Service-Qualität unseres Supports sind wir besonders stolz, das bestätigte uns nun auch der TÜV Saarland mit der Note "sehr gut".

= Kann ich rapidmail kostenlos testen? =

Testen Sie rapidmail kostenlos und unverbindlich und versenden Sie Mailings an bis zu 10 Empfänger immer kostenlos. Darüber hinaus versenden Sie Ihr erstes Mailing an bis zu 2000 Empfänger kostenfrei.

= Was kostet rapidmail? =

Bei rapidmail bezahlen Sie wahlweise pro versendetem Mailing oder Sie buchen eine empfängerbasierte Flatrate mit der Sie unbegrenzt versenden können (ab 9€). Es gibt keine versteckten Zusatzkosten!
Weitere Informationen finden Sie hier: https://www.rapidmail.de/preise?pid=o-wp&tid2=wporg-prices

== Screenshots ==

1. Plugin einfach hinzufügen, z.B. in die Seitenleiste von Wordpress
2. Widget mit Newsletter Anmeldeformular
3. Anmeldeformular zur Abonnentengewinnung in den Kommentaren
4. Verbindung mit rapidmail mit wenigen Klicks
5. Für Profis: Short-Code Integration verwenden

== Changelog ==

= 2.1.5 =
* Prefix API v3 calls with /v1 to prevent updated endpoint versions from breaking the plugin
* Add testing information for Wordpress up to 5.5.1

= 2.1.4 =
* Send accept header to API v3 endpoint explicitly
* Render hidden form fields topmost within enclosing form
* Apply wording changes to plugin settings page

= 2.1.3 =
* Mark compatible with Wordpress 5.0

= 2.1.2 =
* Add support for custom registration success and error messages

= 2.1.1 =
* Fix shortcode always echoed at top of page, should now be inserted at correct position
* Fix submit button label configured in rapidmail not being used in wordpress form

= 2.1.0 =
* Add support to use forms configured in rapidmail directly
* Deprecate APIv1 integration

= 2.0.3 =
* Fix an issue where APIv1 could not be selected

= 2.0.2 =
* Fix an issue where a PHP warning was shown when enabling the plugin

= 2.0.1 =
* Add missing text domain parameter for translation
* Fix use of wrong capability when adding settings menu that caused rapidmail settings to disappear with enabled security plugins

= 2.0 =
* Add support for rapidmail API v3
* Add subscription form shortcode
* Make fields configurable in widget

= 1.1 =
* This version fixes form action update on rapidmail settings changes

= 1.0 =
* Initial version
