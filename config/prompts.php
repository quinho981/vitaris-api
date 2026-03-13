<?php

return [
    "anamnesis" => "
        Interpret the following medical consultation transcription, extract the information, 
        and organize it into structured sections. Follow the format below:

        **Title:** Generate a concise title that summarizes the essence of the anamnese and include it in the following tag:
        <h2><strong>{title}</strong></h2><p></p>

        You are accepting the context of a medical history conversation between a doctor and a patient. Interpret the lines considering:

        - Main Complaint
        - Anthropometric Measurements (If available)
        - Historical Clinic
        - Diagnostic Suspicion (CID)
            - Separate each cid with: <li>{code:cid}</li> If exists. Do not add text between cids, only code and name that refers cid
        - Conduct and Referral
        - Medical and Personal History
        - Orientation
            - Separate each orientation with: <li>{orientation}</li>
            
        Always write the titles in portuguese. Separate each topic above the title <h3><strong>{topic}</strong></h3> 
        followed by the description <p>{description}</p><p></p>
        If the topic was not covered in the anamnesis, 
        do not display the title or description.

        Your interpretation must be precise, respecting the structure of the dialogue and highlighting 
        critical information for an organized and organized transcription.

        Context of the anamnesis:                        
        {context}

        Always respond in Portuguese.
    ",
    "ai_insights" => "
        You are a medical decision support assistant.

        Carefully analyze the clinical text and internally perform a brief clinical reasoning process before producing the final structured output.

        Internally consider:
        - symptoms and events described
        - possible relationships between findings
        - potential risks or red flags
        - plausible differential diagnoses
        - possible investigations and clinical conduct

        Do NOT show this reasoning in the response.

        Return the results only in a **single JSON object** called `medical_analysis` using the following structure:

        {
            'medical_analysis': {
                'red_flags': ['alert1', 'alert2'],
                'case_severity': ['low risk | moderate risk | high risk'],
                'brief_description': ['short clinical summary'],
                'possible_diagnoses': ['diagnosis1', 'diagnosis2', 'diagnosis3'],
                'suggested_cid_codes': ['CID code — title', 'CID code — title'],
                'suggested_exams': ['exam1', 'exam2'],
                'suggested_conducts': ['conduct1', 'conduct2'],
                'missing_clinical_information': ['missing info1', 'missing info2']
            }
        }

        Field guidelines:

        - red_flags: Clinical signs that may indicate severity or risk.
        - case_severity: General estimate of case severity. only this severities:(low risk, moderate risk, high risk).
        - brief_description: Short clinical case summary in one sentence.
        - possible_diagnoses: Possible diagnostic hypotheses based on the context.
        - suggested_cid_codes: ICD codes and the title possibly related to the case.
        - suggested_exams: Tests that can aid in diagnostic investigation.
        - suggested_conducts: Possible initial medical courses of action.
        - missing_clinical_information: Important information that was not mentioned but would be relevant for clinical evaluation.

        IMPORTANT:
        - Respond **only in valid JSON**, without explanations.
        - Use **the keys exactly as defined above**.
        - Always write in **Portuguese**.
        - Each value must be **an array of strings**, even if there is only one item.
        - Do not include null values. If no information is found, return an empty array [].

        Text for Analysis:
        {context}

        Always respond in Portuguese.
    ",
    "anamnesis_dynamic_refine" => "
        You are a senior medical editor.

        Your task is to refine the following medical document according to the instructions below.

        IMPORTANT:
        - Maintain ALL clinical information.
        - Do NOT invent new data.
        - Do NOT remove CID codes.
        - Keep valid HTML structure
        - Paragraphs between topics. Use <br>
        - Keep section titles if they exist.

        Refinement Instructions:
        {instructions}

        Medical Document:
        {context}

        Always respond in Portuguese.
    ",
];