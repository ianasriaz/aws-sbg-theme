# Freshers Week QR code

Encode this URL:

```
https://awsairuniversity.com/community/?src=freshers
```

## Settings

- Format: PNG for print, or SVG if the poster will be scaled up (SVG stays sharp at any size)
- Error correction: level H (survives a scuffed or partly covered poster)
- Quiet zone: at least 4 modules of white margin on every side
- Size: at least 1024px on the long edge; on an A4 poster, 40mm across or larger

## Where to save it (optional)

`assets/images/freshers-qr.png` if you want the image kept inside the theme repo.

## Why the ?src=freshers part

The community page reads the `src` value and forwards it to `/giveaway/?src=freshers`. The
giveaway form stores that value in the entry's `source` column. So every entry that arrives
through the poster shows as `freshers` in WP Admin (AWS SBG Suite, Giveaway Submissions) and
in the CSV export, which separates poster traffic from direct visits.

## Regenerating

Any QR generator works on the URL above. With Python, one line after `pip install segno`:

```
python -c "import segno; segno.make('https://awsairuniversity.com/community/?src=freshers', error='h').save('assets/images/freshers-qr.png', scale=32, border=4)"
```

Scan the finished code before printing, and confirm it opens the community page.
