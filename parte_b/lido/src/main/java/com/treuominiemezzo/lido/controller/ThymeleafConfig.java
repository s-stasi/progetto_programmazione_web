package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.templatemode.TemplateMode;
import org.thymeleaf.templateresolver.WebApplicationTemplateResolver;
import org.thymeleaf.web.IWebApplication;
import org.thymeleaf.web.servlet.JakartaServletWebApplication;

import jakarta.servlet.ServletContext;

public class ThymeleafConfig {

    private static TemplateEngine templateEngine;

    // Metodo per ottenere l'istanza del motore (Singleton pattern)
    public static TemplateEngine getTemplateEngine(ServletContext servletContext) {
        if (templateEngine == null) {
            
            // Inizializza l'applicazione web per Thymeleaf (specifico per Jakarta Servlet 6.0)
            IWebApplication webApplication = JakartaServletWebApplication.buildApplication(servletContext);
            
            // Configura il risolutore dei template (dove cercare i file .html)
            WebApplicationTemplateResolver templateResolver = new WebApplicationTemplateResolver(webApplication);
            
            // Diciamo a Thymeleaf di cercare nella cartella WEB-INF/templates/
            templateResolver.setPrefix("/WEB-INF/templates/");
            templateResolver.setSuffix(".html");
            
            // Impostiamo il formato su HTML standard
            templateResolver.setTemplateMode(TemplateMode.HTML);
            
            // Disabilitiamo la cache per lo sviluppo (in produzione andrebbe a true)
            templateResolver.setCacheable(false);
            
            // Forziamo la codifica UTF-8 per le emoji e i caratteri speciali
            templateResolver.setCharacterEncoding("UTF-8");

            templateEngine = new TemplateEngine();
            templateEngine.setTemplateResolver(templateResolver);
        }
        return templateEngine;
    }
}