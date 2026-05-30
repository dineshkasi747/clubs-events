import fitz
import re
import json
import os
import argparse
import sys

def main():
    # Force standard output to UTF-8 to prevent Windows CP1252/ANSI encoding mismatch
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

    parser = argparse.ArgumentParser()
    parser.add_argument('--pdf', required=True, help='Path to the uploaded PDF file')
    parser.add_argument('--out-dir', required=True, help='Directory to save extracted images')
    parser.add_argument('--prefix', default='', help='Prefix for extracted images')
    args = parser.parse_args()

    pdf_path = args.pdf
    out_dir = args.out_dir
    prefix = args.prefix

    if not os.path.exists(pdf_path):
        print(json.dumps({"error": f"PDF file not found: {pdf_path}"}))
        sys.exit(1)

    os.makedirs(out_dir, exist_ok=True)

    try:
        doc = fitz.open(pdf_path)
    except Exception as e:
        print(json.dumps({"error": f"Failed to open PDF: {str(e)}"}))
        sys.exit(1)

    events = []
    for page_num, page in enumerate(doc):
        text = page.get_text()
        
        # Check if page has any event or activity keywords
        has_event = re.search(r"(Event:|EVENT DETAILS|EVENT DTEAILS|EVENT DESCRIPTION|Activity Name|Activity Date)", text, re.IGNORECASE)
        if not has_event:
            continue

        # Extract Title / Activity Name (with multi-line support up to the next keyword)
        title = ""
        title_match = re.search(
            r"\b(?:Event|Activity Name)[:]?\s*(.*?)(?=\bActivity Date|\bDate|\bLocation|\bPlace|\bPLACE|\bDATE|$)",
            text,
            re.DOTALL | re.IGNORECASE
        )
        if title_match:
            title = re.sub(r"\s+", " ", title_match.group(1)).strip()
            title = re.sub(r"^[:\- \.]+", "", title).strip()

        # Extract Date / Activity Date
        date = ""
        date_match = re.search(r"\b(?:Date|Activity Date)[:]?\s*(.*)", text, re.IGNORECASE)
        if date_match:
            date = re.sub(r"^[:\- \.]+", "", date_match.group(1).strip()).strip()

        # Clean up header swapping typo (e.g. Activity Name has date, Activity Date has title)
        # Check if title matches date format (e.g. "10-12-2017" or "3-02-2017")
        if title and re.match(r"^[\d\-\.\/ ]+$", title):
            # Swap title and date if date has actual words (longer than 10 characters or contains alphabet letters)
            if date and (len(date) > 10 or re.search(r"[a-zA-Z]", date)):
                temp = title
                title = date
                date = temp

        # Clean up date to strip any trailing dots or spaces
        if date:
            date = re.sub(r"[\s\.\-]+$", "", date).strip()

        # Extract Place / Location
        place = ""
        place_match = re.search(r"\b(?:Place|Location)[:]?\s*(.*)", text, re.IGNORECASE)
        if place_match:
            place = re.sub(r"^[:\- \.]+", "", place_match.group(1).strip()).strip()
            place = re.sub(r"[\s\.\-]+$", "", place).strip()

        # Extract Volunteers Count
        volunteers = ""
        volunteer_match = re.search(
            r"\b(?:No\s*of\s*Volunteers|Volunteers\s*involved)[:]?\s*(\d+)",
            text,
            re.IGNORECASE
        )
        if volunteer_match:
            volunteers = volunteer_match.group(1).strip()

        # Extract Description
        description = ""
        desc_match = re.search(
            r"\bEvent\s*Description\s*:(.*?)(Volunteers distributing|Orphanage visit|Volunteers participating|$)",
            text,
            re.DOTALL | re.IGNORECASE
        )
        if not desc_match:
            # Fallback regex matching general EVENT DESCRIPTION
            desc_match = re.search(r"EVENT DESCRIPTION[:]?\s*(.*)", text, re.DOTALL | re.IGNORECASE)

        if desc_match:
            description = re.sub(r"\s+", " ", desc_match.group(1)).strip()
            # Clean up trailing non-alphanumeric chars or encoding artifacts
            description = re.sub(r"[\s\.\-\,]+$", "", description).strip()
        else:
            # Fallback: if description is not explicitly found, use the long title/Activity Name as description
            if len(title) > 60:
                description = title

        # Construct a clean, shortened title if it is too long or contains description text
        if title and (len(title) > 60 or description == title):
            cleaned_title = title.replace("Distribution of of", "Distribution of").replace("Distribution of", "Distribution of")
            # Split by common boundaries to find a clean, short title clause
            clauses = re.split(r"[\(,\.;]|\band\b", cleaned_title, flags=re.IGNORECASE)
            first_clause = clauses[0].strip()
            if len(first_clause) > 50:
                words = first_clause.split()
                temp = ""
                for word in words:
                    if len(temp + " " + word) > 47:
                        break
                    temp = (temp + " " + word).strip()
                first_clause = temp + "..."
            title = first_clause

        # Fallback for empty title
        if not title:
            title = f"Historical Event {page_num + 1}"

        # Extract images
        image_paths = []
        image_list = page.get_images(full=True)
        for img_index, img in enumerate(image_list):
            xref = img[0]
            try:
                base_image = doc.extract_image(xref)
                image_bytes = base_image["image"]
                image_ext = base_image["ext"]
                image_name = f"{prefix}event_{page_num+1}_{img_index}.{image_ext}"
                image_path = os.path.join(out_dir, image_name)
                with open(image_path, "wb") as image_file:
                    image_file.write(image_bytes)
                image_paths.append(image_name)
            except Exception:
                pass

        events.append({
            "title": title,
            "date": date,
            "place": place,
            "volunteers": volunteers,
            "description": description,
            "images": image_paths
        })

    print(json.dumps(events, indent=4, ensure_ascii=False))

if __name__ == '__main__':
    main()
