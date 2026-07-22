package com.treuominiemezzo.lido.controller;

import org.thymeleaf.TemplateEngine;
import org.thymeleaf.templatemode.TemplateMode;
import org.thymeleaf.templateresolver.ServletContextTemplateResolver;


import javax.servlet.ServletContext;

public class ThymeleafConfig {

  private static TemplateEngine templateEngine;

  public static TemplateEngine getTemplateEngine(ServletContext servletContext) {
    if (templateEngine == null) {

     ServletContextTemplateResolver templateResolver = new ServletContextTemplateResolver(servletContext);
      templateResolver.setPrefix("/WEB-INF/templates/");
      templateResolver.setSuffix(".html");
      templateResolver.setTemplateMode(TemplateMode.HTML);
      templateResolver.setCacheable(false);
      templateResolver.setCharacterEncoding("UTF-8");

      templateEngine = new TemplateEngine();
      templateEngine.setTemplateResolver(templateResolver);
    }
    return templateEngine;
  }
}